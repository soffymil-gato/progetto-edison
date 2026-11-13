# Progetto Edison

Piattaforma web B2B per Edison, azienda che commercializza prodotti e servizi per il settore energetico. Comprende tre aree:

| Area | Percorso (sviluppo) | Stato |
|---|---|---|
| **Sito pubblico** | `/` | Pagine complete. I testi aziendali sono **da fornire** e marcati come segnaposto |
| **Area clienti** | `/area-clienti` | Accesso, recupero password, attivazione via invito, dashboard, profilo. Catalogo e ordini **in attesa dell'integrazione con il database aziendale** |
| **Gestionale** | `/gestionale` | Accesso, utenti interni, ruoli e permessi, registro attività, impostazioni aziendali, richieste di contatto |

In produzione ogni area è pensata su un proprio sottodominio (`EDISON_*_DOMAIN`).

Lo stato dettagliato è in [`docs/stato-avanzamento.md`](docs/stato-avanzamento.md): cosa è realizzato e verificato, cosa è segnaposto, cosa è bloccato.

## Stack

- **Backend:** Laravel 13, PHP ≥ 8.3 con `strict_types`, Larastan livello 8.
- **Database:** Microsoft SQL Server, tramite driver ufficiale `pdo_sqlsrv` e ODBC Driver 18.
  - Database applicativo dedicato `EdisonPortal`.
  - Database aziendale in sola lettura (connessione `legacy`, non ancora usata).
- **Frontend:** React 19, TypeScript strict, Inertia.js 3, Tailwind CSS 4, primitive Radix, icone Lucide, font self-hosted.
- **Sicurezza:**
  - Argon2id;
  - sessioni, cookie e guard separati per area;
  - CSRF (verifica dell'origine con token di riserva);
  - CSP con nonce;
  - RBAC granulare;
  - registro attività append-only.
- **Test:** Pest su SQL Server reale, Playwright end-to-end con axe (WCAG 2.2 AA).

Le motivazioni della scelta sono in [`docs/analisi/report-analisi-preliminare.md`](docs/analisi/report-analisi-preliminare.md) e [`docs/adr/`](docs/adr).

## Requisiti

- PHP 8.3+ con estensioni `pdo_sqlsrv`, `intl`, `mbstring`.
- Microsoft ODBC Driver 18 for SQL Server.
- Composer 2.
- Node.js 22+.
- SQL Server 2017 o successivo. In sviluppo va bene SQL Server Developer Edition o il container ufficiale `mcr.microsoft.com/mssql/server`.

## Installazione in sviluppo

1. Installa le dipendenze PHP:

   ```bash
   composer install
   ```

2. Installa le dipendenze frontend:

   ```bash
   npm install
   ```

3. Crea il file di configurazione locale:

   ```bash
   cp .env.example .env
   ```

4. Genera la chiave applicativa:

   ```bash
   php artisan key:generate
   ```

5. Valorizza in `.env` le credenziali del **database di sviluppo** (`DB_*`). Non usare mai credenziali di produzione in sviluppo.

6. Crea le tabelle e i ruoli predefiniti:

   ```bash
   php artisan migrate --seed
   ```

7. Crea il primo Super Admin. Riceverà un invito per scegliere la propria password:

   ```bash
   php artisan edison:create-super-admin --print-link
   ```

8. Compila gli asset frontend:

   ```bash
   npm run build
   ```

9. Avvia l'applicazione:

   ```bash
   php artisan serve
   ```

Durante lo sviluppo del frontend avvia anche Vite con hot reload:

```bash
npm run dev
```

Le email (inviti, recupero password) sono messe in coda. In locale avvia un worker per elaborarle (con `MAIL_MAILER=log` finiscono in `storage/logs/laravel.log`):

```bash
php artisan queue:work
```

Per creare un account cliente di prova (solo sviluppo o staging; il comando si rifiuta di girare in produzione):

```bash
php artisan edison:portal-invite cliente@example.test --name="Nome" --customer-ref="TEST-001" --print-link
```

## Comandi utili

| Comando | Scopo |
|---|---|
| `php artisan edison:create-super-admin` | Crea un Super Admin e gli invia l'invito di attivazione |
| `php artisan edison:rbac-sync` | Crea i permessi registrati e i ruoli predefiniti mancanti (idempotente) |
| `php artisan edison:release-check` | **Blocca il rilascio** finché restano testi segnaposto, dati aziendali mancanti, il logo ufficiale o il mittente email non sono configurati |
| `php artisan edison:portal-invite` | Account cliente di prova (solo non produzione) |

## Test e qualità

| Controllo | Comando |
|---|---|
| Test backend (richiede `.env.testing`, vedi `.env.testing.example`) | `vendor/bin/pest` |
| Analisi statica | `vendor/bin/phpstan analyse --memory-limit=1G` |
| Stile PHP | `vendor/bin/pint --test` |
| Tipi TypeScript | `npm run typecheck` |
| ESLint | `npm run lint` |
| Prettier | `npm run format:check` |
| Test end-to-end (database dedicato `EdisonPortal_e2e`) | `npm run test:e2e` |

La CI (`.github/workflows/ci.yml`) esegue tutti i controlli su un SQL Server usa e getta, con password casuale per ogni esecuzione.

## Struttura

```
app/
  Domain/            logica di dominio: Identity, Audit, Settings, Contact, Content
  Http/Controllers/  Public, Portal, Admin, Auth (condiviso tra area clienti e gestionale)
  Http/Middleware/   SetArea, EnforceSessionPolicy, SecurityHeaders, PreventRequestForgery, HandleInertiaRequests
  Support/           Area, Navigation, Seo, Like
config/edison.php    parametri tecnici della piattaforma
database/migrations/ SOLO il database applicativo EdisonPortal
docs/                analisi, ADR, sicurezza, deployment, stato di avanzamento
lang/it/             messaggi in italiano
resources/content/   testi del sito (segnaposto marcati «[DA FORNIRE: …]»)
resources/js/        design-system, layouts, pages/{Public,Portal,Admin,Auth,Account}, features, lib
routes/              public.php, portal.php, admin.php
tests/               Feature, Unit (Pest) ed e2e (Playwright)
```

## Regole del progetto

- Il database aziendale SQL Server si **legge e non si scrive**.
- Nessun dato aziendale inventato: i contenuti mancanti sono marcati e bloccano il rilascio.
- Nessun segreto nel codice: solo variabili d'ambiente o secret manager.
- Ogni operazione è autorizzata lato server; l'interfaccia non è una protezione.
