# Deployment

> **L'infrastruttura di Edison non è ancora nota** (domanda I1 del report): non sappiamo se l'applicazione girerà su Linux/Docker o su Windows Server/IIS. Questo documento descrive i requisiti e i passaggi indipendenti dalla piattaforma. La procedura definitiva va completata quando l'infrastruttura sarà confermata.

## Ambienti

| Ambiente | Database | Dati | Note |
|---|---|---|---|
| development | `EdisonPortal_dev` (istanza locale) | sintetici | `APP_DEBUG=true`, `MAIL_MAILER=log` |
| staging | `EdisonPortal` su istanza di staging | copia anonimizzata | Stessa configurazione della produzione |
| production | `EdisonPortal` | reali | `APP_ENV=production`, `APP_DEBUG=false` |

## Processi necessari in esecuzione

1. **Web:** PHP-FPM dietro Nginx (Linux) oppure FastCGI su IIS (Windows), con document root `public/`.
2. **Worker delle code:**

   ```bash
   php artisan queue:work --tries=3 --max-time=3600
   ```

   Va gestito da supervisor, systemd o un servizio Windows. Senza worker **le email** (inviti, recupero password, notifiche dei contatti) **non partono**.
3. **Scheduler** (per le attività pianificate future): cron ogni minuto.

   ```bash
   php artisan schedule:run
   ```

## Variabili d'ambiente

Il riferimento completo è `.env.example`.

**Obbligatorie in produzione:**

- `APP_KEY`, `APP_URL`;
- `EDISON_PUBLIC_DOMAIN`, `EDISON_PORTAL_DOMAIN`, `EDISON_ADMIN_DOMAIN`: un sottodominio per area;
- `DB_*` con `DB_ENCRYPT=yes` e `DB_TRUST_SERVER_CERTIFICATE=false`;
- `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`;
- `TRUSTED_PROXIES`, se c'è un reverse proxy TLS;
- `MAIL_*` con mittente verificato (SPF/DKIM/DMARC);
- `EDISON_LOGO_PATH`.

I segreti vanno solo nelle variabili del server o in un secret manager, **mai** nel repository.

## Rilascio (ogni versione)

1. Il gate di rilascio deve passare: non restano segnaposto, dati aziendali mancanti o logo assente.

   ```bash
   php artisan edison:release-check
   ```

2. Installa le dipendenze PHP senza quelle di sviluppo:

   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. Installa le dipendenze frontend:

   ```bash
   npm ci
   ```

4. Compila gli asset:

   ```bash
   npm run build
   ```

5. **Backup verificato** del database `EdisonPortal`. Le migrazioni non toccano il database aziendale.
6. Esegui le migrazioni con il login `edison_migrator`, solo dopo autorizzazione:

   ```bash
   php artisan migrate --force
   ```

7. Sincronizza ruoli e permessi:

   ```bash
   php artisan edison:rbac-sync
   ```

8. Metti in cache la configurazione:

   ```bash
   php artisan config:cache
   ```

9. Metti in cache le rotte:

   ```bash
   php artisan route:cache
   ```

10. Metti in cache le viste:

    ```bash
    php artisan view:cache
    ```

11. Riavvia i worker delle code:

    ```bash
    php artisan queue:restart
    ```

## Rollback

- **Codice:** ridistribuire la versione precedente.
- **Database:** ogni migrazione ha il proprio `down()`. Se una migrazione ha alterato dati, la via preferita è il ripristino dal backup fatto prima del rilascio.

## HTTPS e intestazioni

- TLS 1.2 o superiore, terminato dal reverse proxy. Configurare `TRUSTED_PROXIES` con l'indirizzo del proxy.
- L'applicazione invia già:
  - HSTS (su richieste HTTPS);
  - CSP con nonce;
  - `X-Frame-Options`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`;
  - `X-Robots-Tag: noindex` sulle aree riservate.
- Facoltativo, consigliato: rendere raggiungibile il gestionale solo da VPN o da IP aziendali.

## Backup, log e monitoraggio

- **Backup** del database `EdisonPortal`: full, differenziali e log, secondo la policy del DBA. Il ripristino va provato periodicamente.
- **Log applicativi** in `storage/logs`. I log non contengono password né token. Va prevista la rotazione.
- **Monitoraggio:** endpoint di salute `GET /up`. Va monitorata anche la coda (job falliti in `failed_jobs`).

## Da definire con Edison

- Piattaforma di esecuzione: Linux/Docker o Windows Server/IIS.
- Strumenti di monitoraggio esistenti.
- Provider email.
- Gestione dei certificati.
- Finestra di manutenzione.
