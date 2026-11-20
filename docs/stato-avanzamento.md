# Stato di avanzamento

**Aggiornato al:** 13/11/2026

**Perimetro autorizzato:**

- stack Laravel;
- attività **indipendenti dal database aziendale**;
- database SQL Server.

Il database aziendale **non è stato analizzato né toccato**: manca ancora il materiale dell'Appendice A del report.

## 1. Realizzato e verificato

| Ambito | Contenuto | Verifica eseguita |
|---|---|---|
| Fondamenta | Laravel 13 su SQL Server (`pdo_sqlsrv`, ODBC 18), database applicativo `EdisonPortal`, migrazioni solo applicative | Migrazioni eseguite su SQL Server 2025 Developer (locale) |
| Isolamento delle aree | Sessioni, cookie, tabelle di sessione e cookie CSRF distinti; guard `staff` e `customer`; bundle JavaScript separati | Test Pest (`AreaIsolationTest`) e verifica degli header HTTP |
| Autenticazione | Argon2id; throttling per identificativo e IP; risposte non enumerabili e tempi uniformi; rigenerazione della sessione; inattività 30/60 minuti e scadenza assoluta 12 ore; logout; recupero password; attivazione tramite invito (token salvato solo come hash) | Test Pest `LoginTest`, `PasswordResetTest`, `InvitationTest`; flusso reale nel browser |
| RBAC | 7 permessi granulari, 5 ruoli predefiniti configurabili; regole anti-escalation (Super Admin protetto, nessuna modifica ai propri ruoli, almeno un Super Admin attivo) | Matrice dei permessi su **tutte** le rotte del gestionale; test di amministrazione |
| Registro attività | Append-only su tre livelli (modello, trigger SQL Server, `DENY` proposto); i dati sensibili non vengono mai registrati | Test con tentativo di `UPDATE`/`DELETE` SQL diretto respinto |
| Gestionale | Panoramica, utenti interni, ruoli e permessi, registro attività, impostazioni aziendali, richieste di contatto, profilo | Test Pest ed E2E; verifica visuale desktop e mobile |
| Area clienti | Accesso con email o nome utente, recupero password, invito, dashboard, profilo e cambio password | Test Pest ed E2E |
| Sito pubblico | Home, Azienda, Prodotti, Servizi, Contatti (modulo funzionante con honeypot e rate limit), Privacy, Cookie; SEO lato server, sitemap, robots, Open Graph; dati strutturati solo con dati reali | Test Pest ed E2E; axe WCAG 2.2 AA senza violazioni serie o critiche |
| Sicurezza HTTP | CSP con nonce, HSTS, `nosniff`, `frame-ancestors 'none'`, `noindex` sulle aree riservate | Test Pest; nessuna violazione CSP in console |
| Qualità | Larastan livello 8, Pint, TypeScript strict, ESLint, Prettier, `npm audit` / `composer audit` puliti | Eseguiti localmente |
| CI | GitHub Actions: frontend, backend con SQL Server usa e getta (password casuale per esecuzione), E2E, controllo di rilascio manuale | Esito dell'esecuzione su GitHub riportato a parte |

**Ultimi risultati locali:**

- **Pest:** 123 test, 522 asserzioni, tutti superati, su SQL Server reale.
- **Playwright:** 36 test superati (18 scenari × desktop e mobile).

## 2. Segnaposto dichiarati (bloccano il rilascio)

`php artisan edison:release-check` fallisce finché:

- i **44 testi** del sito in `resources/content/site.php` non sono sostituiti con i testi forniti da Edison;
- le **impostazioni aziendali** non sono compilate dal gestionale (ragione sociale, sede, P.IVA, contatti, orari, sede e orari di ritiro, destinatario del modulo contatti);
- il **logo ufficiale** non è configurato (`EDISON_LOGO_PATH`);
- l'**indirizzo mittente delle email** non è configurato (`MAIL_FROM_ADDRESS`): senza, inviti e recupero password non partono;
- le immagini delle categorie di prodotto non sono fornite: oggi sono illustrazioni astratte etichettate «Immagine da fornire»;
- i testi di Privacy e Cookie Policy non sono validati dal titolare o dal consulente. L'elenco tecnico dei cookie è già reale e verificato.

## 3. Non ancora realizzato

### In attesa dell'analisi del database aziendale

- Catalogo e listino, carrello, conferma ordine transazionale, storico e stati, ritiri.
- Gestione clienti e account clienti dal gestionale.
- Gestione prodotti.
- Statistiche, report ed export.
- Notifiche sugli ordini.
- Documenti per cliente.

### Non richiesto o da approvare

- Autenticazione a due fattori per il personale (consigliata).
- Controllo delle password compromesse tramite servizio esterno.
- Retention automatica delle richieste di contatto (`EDISON_RETENTION_CONTACT_MONTHS`).
- Accesso del personale con account Microsoft (Entra ID).

## 4. Informazioni ancora necessarie

Sono quelle del report (§7). In particolare:

- export dello schema SQL Server (Appendice A);
- software proprietario dei dati;
- presenza di account clienti;
- regole commerciali (IVA, arrotondamenti, disponibilità);
- infrastruttura di hosting;
- logo e contenuti;
- testi legali.

## 5. Note sull'ambiente di sviluppo

Nell'ambiente cloud di sviluppo SQL Server 2025 Developer, ODBC 18 e `pdo_sqlsrv` sono installati **solo per sviluppo e test**. L'edizione Developer non è licenziata per la produzione.
