# Progetto Edison — Report di analisi preliminare (Fase 1)

| Revisione | Data | Modifica |
|---|---|---|
| 1 | 13/11/2026 | Prima emissione (database ipotizzato MySQL) |
| 2 | 13/11/2026 | **Il database aziendale è Microsoft SQL Server.** Il report e il kit di estrazione (Appendice A) sono stati riscritti per SQL Server. Lo stack Laravel è approvato dal committente. Le nuove tabelle della piattaforma vanno anch'esse su SQL Server |

**Stato:**

- **Approvati:** stack Laravel e avvio delle attività indipendenti dal database aziendale.
- **In attesa:** il materiale per l'analisi del database (Appendice A).

> **Nessun accesso** è stato effettuato al database aziendale. Lo schema esistente **non è stato ipotizzato**. Ogni modifica a database e schema resta soggetta ad approvazione esplicita, backup verificato e piano di rollback.

---

## 0. Sintesi

| Tema | Esito |
|---|---|
| Repository | All'inizio dell'analisi conteneva solo un `README.md` di esempio: un progetto da costruire da zero. |
| Database aziendale | **Microsoft SQL Server**. Versione, edizione, schema, collation e modalità di autenticazione sono **da rilevare**: non abbiamo accesso, dump o export. |
| Account clienti esistenti | **Sconosciuto**: è la prima domanda bloccante (§8, D1). |
| Stack (approvato) | **Laravel + Inertia.js + React + TypeScript strict + Tailwind CSS**, come monolite modulare. Accesso a SQL Server con il driver ufficiale Microsoft (`pdo_sqlsrv` + ODBC Driver 18). |
| Dati della piattaforma | **Database dedicato `EdisonPortal`** sulla stessa istanza SQL Server (raccomandato), oppure schema `portal` nel database esistente. Il database aziendale resta in **sola lettura**. |
| Blocchi principali | 1) export dello schema SQL Server; 2) software proprietario dei dati (ERP o altro gestionale?); 3) regole commerciali; 4) infrastruttura (Windows Server/IIS o Linux/Docker); 5) logo e contenuti ufficiali; 6) testi legali. |

---

## 1. Analisi del repository e dell'infrastruttura

### 1.1 Repository `soffymil-gato/progetto-edison`

Lo stato rilevato all'inizio dell'analisi è il seguente.

| Elemento | Rilevato |
|---|---|
| Commit | 1 (`6dc6cdd` — README di esempio con sezioni vuote) |
| Codice, dipendenze, configurazioni, CI, autenticazione, asset | assenti |
| Branch | `claude/pensive-lamport-eyzzjk`, `claude/inspiring-ritchie-sav3g6`. **Non esiste `main`** |
| Issue / PR | nessuna |

**Conclusione:** progetto da costruire da zero, con nulla da preservare oltre al README.

### 1.2 Ambiente di sviluppo (container cloud effimero)

- Ubuntu 24.04, PHP 8.3, Node 22, Composer, Playwright con Chromium.
- Per lo sviluppo e i test sono stati installati **SQL Server 2025 edizione Developer** (gratuita, **solo** per sviluppo e test), **Microsoft ODBC Driver 18** ed estensione PHP `pdo_sqlsrv`.

Lo sviluppo **non si collega mai al database di produzione**. Lavorerà su uno schema ricreato dal **dump del solo schema** fornito da Edison, con **dati sintetici** chiaramente marcati. Il collaudo con dati reali va fatto in **staging** con una copia anonimizzata.

**Infrastruttura di Edison: sconosciuta.** Con SQL Server è probabile un contesto Microsoft (Windows Server, Active Directory). Ne dipende il modo di eseguire l'applicazione (§8, sezione I):

- **Linux/Docker:** consigliato. Nginx + PHP-FPM con ODBC Driver 18.
- **Windows Server + IIS:** PHP via FastCGI con i driver `sqlsrv` per Windows. Supportato, ma con più manutenzione manuale.

---

## 2. Analisi del database SQL Server

### 2.1 Stato

**Non eseguibile con le informazioni disponibili.** Non ci sono istanza, nome del database, credenziali, script dello schema, file `.dacpac`, diagramma ER o documentazione del software che oggi usa il database.

Le voci richieste da `DATABASE_ANALYSIS.md` §4–§5 restano **"Da rilevare"**. Per SQL Server comprendono:

- versione, edizione e livello di compatibilità;
- collation di istanza e di database;
- isolamento (`READ_COMMITTED_SNAPSHOT`);
- recovery model;
- schemi, tabelle, colonne, PK, FK, indici (anche filtrati), vincoli CHECK;
- viste, stored procedure, funzioni, trigger, sinonimi, sequence;
- job di SQL Server Agent (l'equivalente degli eventi schedulati);
- linked server;
- Change Tracking e CDC;
- tabelle temporali;
- utenti, ruoli e permessi.

### 2.2 Cosa serve, nell'ordine di preferenza

1. **Script del solo schema** oppure **file `.dacpac`** (Appendice A.1).
2. **Esito delle query di inventario** dell'Appendice A.2: sola lettura su viste di catalogo `sys.*`, nessun dato aziendale.
3. **Nome e versione del software** che usa oggi il database (ERP, gestionale, applicazioni .NET o Access, ecc.) e la relativa documentazione.
4. **Diagramma ER**, se esiste. SSMS può generarlo dai *Database Diagrams*.
5. **Pochi record anonimizzati**, solo se lo schema non basta a capire la semantica.
6. In alternativa, un **login in sola lettura** su una **copia di staging**, con credenziali consegnate tramite un canale sicuro. **Mai in chat né nel repository.**

### 2.3 Deliverable dell'analisi del database (dalla ricezione del materiale)

Coprono i punti 1–15 di `DATABASE_ANALYSIS.md` §14:

- inventario, diagramma ER e dizionario dati;
- mappa account ↔ cliente;
- mappa prodotti ↔ categorie ↔ prezzi ↔ disponibilità;
- mappa ordini e righe;
- matrice di lettura/scrittura e sistemi proprietari dei dati;
- gap analysis;
- estensioni proposte;
- query e indici suggeriti (non eseguiti);
- strategia transazionale e di autenticazione;
- piano di backup e rollback;
- informazioni residue da chiedere a Edison.

Stima: **3–5 giorni lavorativi**.

### 2.4 Verifiche specifiche su SQL Server

- **Edizione.** *Express* ha un limite di 10 GB per database e limiti di memoria e CPU. Ne dipende il dimensionamento.
- **Versione e livello di compatibilità.** Laravel supporta SQL Server 2017 e successivi. Le versioni fuori supporto Microsoft sono un rischio di sicurezza.
- **Collation.**
  - Va capito se le colonne testo sono `VARCHAR` con code page o `NVARCHAR` Unicode, per evitare caratteri corrotti.
  - Una collation diversa tra database o `tempdb` causa errori di *collation conflict* nelle join tra database.
- **Isolamento.** Va verificato se `READ_COMMITTED_SNAPSHOT` è attivo: senza, le letture del portale possono bloccare o essere bloccate dal gestionale esistente.
- **Credenziali clienti**, se esistono: l'algoritmo si riconosce **senza leggere gli hash**, dalla distribuzione di prefisso e lunghezza (query A.3). In ambienti Microsoft sono frequenti ASP.NET Identity (PBKDF2), il vecchio ASP.NET Membership (SHA-1, debole) e `HASHBYTES`.
- **Trigger, stored procedure e job di Agent** che agiscono su ordini o magazzino: un nostro inserimento potrebbe innescare logiche del gestionale esistente.
- **Change Tracking / CDC.** Se attivi, sono un canale di sincronizzazione già pronto per catalogo e giacenze.
- **Linked server e sinonimi.** Rivelano dati che in realtà risiedono in altri sistemi.
- **Listini multipli.** La scelta del listino pubblico va **approvata da Edison**.

---

## 3. Tecnologie e confronto (aggiornato per SQL Server)

| Criterio | **A. Laravel + Inertia + React/TS** (approvato) | B. Next.js full-stack | C. Next.js + NestJS | D. Laravel + Filament | E. ASP.NET Core + React |
|---|---|---|---|---|---|
| Supporto SQL Server | **Ufficiale** (driver `sqlsrv` Microsoft, migrazioni, lock) | Prisma e Kysely (tedious) | idem B | Ufficiale | **Nativo** (EF Core) |
| Sicurezza integrata | Nativa e matura | Da assemblare | Parziale | Nativa | Nativa (Identity, antiforgery) |
| Argon2id | Nativo | Libreria | Libreria | Nativo | Libreria (default PBKDF2) |
| Code, email, scheduler | Nativi | Servizi aggiuntivi | idem | Nativi | Hosted services o librerie |
| Design system unico React | Sì | Sì | Sì | No | Sì (SPA separata + API) |
| Componenti da gestire | 1 app + worker | 1 app + worker | 2 servizi | 1 app + worker | API + SPA + worker |
| Integrazione Windows/AD | Possibile (LDAP/OIDC) | Possibile | Possibile | Possibile | **Molto naturale** |
| Tempo di sviluppo | Medio | Medio-alto | Alto | Basso, ma grafica limitata | Medio-alto |

**Perché Laravel resta la scelta:**

- SQL Server è **supportato ufficialmente** (driver Microsoft, Eloquent, migrazioni, `lockForUpdate`);
- le funzioni richieste sono già integrate: autenticazione, CSRF, rate limiting, reset password, code, notifiche, policy;
- c'è un unico design system React per le tre aree.

**Quando rivalutare l'opzione E (.NET):** solo se l'IT di Edison **impone** uno stack esclusivamente Microsoft, per competenze interne o per policy di hosting su IIS.

---

## 4. Stack (approvato)

| Livello | Scelta |
|---|---|
| Backend | Laravel 13, PHP ≥ 8.3 con `strict_types`, Larastan |
| Frontend | React 19, TypeScript strict, Inertia.js 3, Vite, Tailwind CSS 4, primitive Radix, icone Lucide |
| Dati | **SQL Server**: connessione `legacy` (sola lettura, database aziendale) e connessione `portal` (database `EdisonPortal`). Driver `pdo_sqlsrv`, ODBC Driver 18, `Encrypt=yes` |
| Autenticazione | Guard `customer` e `staff` separate, sessioni lato server, Argon2id |
| Autorizzazione | Policy e Gate + RBAC granulare (`spatie/laravel-permission`, compatibile SQL Server) |
| Code e notifiche | Laravel Queue (driver database su SQL Server, o Redis), Notifications, Scheduler |
| Test | Pest su SQL Server reale (locale e in CI con il container ufficiale Microsoft), Playwright |
| Esecuzione | Docker su Linux (raccomandato) o Windows Server/IIS, da decidere con l'IT di Edison |

---

## 5. Architettura proposta

### 5.1 Vista d'insieme

```
Internet (solo HTTPS)
  │
  ▼
Reverse proxy: TLS, HSTS, header di sicurezza
  ├── www.<dominio>         → Sito pubblico
  ├── clienti.<dominio>     → Portale clienti   (cookie di sessione dedicato)
  └── gestionale.<dominio>  → Gestionale        (cookie dedicato; opzionale: solo VPN/IP aziendali)
  │
  ▼
Applicazione Laravel (monolite modulare) + worker code + scheduler
  │
  ▼
Istanza SQL Server (esistente)
  ├── Database aziendale    ← login edison_ro       (solo SELECT sugli oggetti necessari, o su viste dedicate)
  └── Database EdisonPortal ← login edison_app      (DML sullo schema applicativo; audit solo INSERT/SELECT)
       (nuovo)              ← login edison_migrator (DDL, usato solo durante il deploy)
```

- In produzione ogni area ha il proprio **sottodominio**, ciascuno con il proprio cookie. Le **guard** sono separate e i **bundle frontend** sono distinti per area.
- Il browser non accede mai al database.

### 5.2 Moduli

Restano quelli della revisione 1:

- `Identity`, `Customers`, `Catalog`, `Ordering`, `Pickup`;
- `Notifications`, `Documents`, `Reporting`, `Audit`, `Settings`;
- lo strato **`Infrastructure/Legacy`**, unico punto del codice che conosce tabelle e colonne reali del database aziendale.

### 5.3 Strategia dati su SQL Server

1. **Il database aziendale si legge e non si scrive.** Il login `edison_ro` ha `SELECT` solo sugli oggetti necessari. In alternativa, se il DBA preferisce, si crea uno schema di **viste dedicate** (es. `web.*`) che espone solo i campi autorizzati.
2. **Database dedicato `EdisonPortal`** sulla stessa istanza (raccomandato):
   - backup, permessi e ciclo di vita separati;
   - nessun oggetto aggiunto al database aziendale;
   - join per i report possibili con nomi a tre parti.

   **Alternativa:** schema `portal` nel database esistente, se Edison preferisce un solo database da gestire.
3. **Nessuna FOREIGN KEY verso il database aziendale.** Tra database diversi non sono possibili, e anche nello stesso database bloccherebbero il gestionale esistente. I riferimenti sono logici e un job periodico ne verifica la coerenza.
4. **Isolamento:** `READ_COMMITTED_SNAPSHOT ON` su `EdisonPortal`, per ridurre i blocchi tra letture e scritture.
5. **Collation** di `EdisonPortal` allineata a quella del database aziendale, per evitare conflitti nelle join. Testi in `NVARCHAR` (Unicode).
6. **Tipi:**
   - importi `DECIMAL(19,4)` per i prezzi unitari e `DECIMAL(19,2)` per i totali, **mai `MONEY` né `FLOAT`**;
   - date `DATETIME2`, in UTC.
7. **Campi univoci che ammettono NULL** (es. username facoltativo): SQL Server consente **un solo NULL** in un indice univoco, quindi servono **indici univoci filtrati** (`WHERE col IS NOT NULL`).
8. **Numerazione ordini:** un oggetto `SEQUENCE` per anno, oppure una tabella contatori bloccata in transazione. La scelta dipende da come Edison vuole gestire i numeri persi in caso di annullamento della transazione.

### 5.4 Modello dati applicativo — bozza condizionata

Le tabelle sono le stesse della revisione 1, ora nel database `EdisonPortal`:

- `portal_accounts`, `account_invitations`, `password_reset_tokens`;
- `staff_users` e tabelle RBAC;
- `carts`, `cart_items`;
- `orders`, `order_lines`, `order_status_history`;
- `notifications`, `notification_preferences`, `email_deliveries`;
- `communications`, `documents`, `settings`;
- `audit_logs` (append-only);
- `contact_requests`, `support_requests`;
- `stock_reservations`, solo se approvata.

Le tabelle legate agli ordini **non saranno create** prima dell'analisi del database aziendale e dell'approvazione.

### 5.5 Conferma ordine su SQL Server

Il flusso è lo stesso della revisione 1:

1. il client invia solo prodotto, quantità e chiave di idempotenza;
2. il server ricava il cliente dalla sessione e rilegge prezzi e disponibilità;
3. in **una transazione** su `EdisonPortal`:
   - lock delle righe interessate (`lockForUpdate`, che in SQL Server diventa `WITH (ROWLOCK, UPDLOCK, HOLDLOCK)`);
   - numerazione;
   - testata e righe con fotografia dei dati;
   - storico stati, audit e outbox delle notifiche;
4. le email partono solo dopo il commit.

La chiave di idempotenza ha un **vincolo UNIQUE** per account.

**Rispetto a MySQL:** tutte le tabelle SQL Server sono transazionali, quindi **il rischio "MyISAM" non esiste**. Resta però un vincolo: se la disponibilità è gestita dal software aziendale, il portale **non scrive** nel suo database. Le opzioni sono:

- (a) disponibilità informativa;
- (b) impegnato calcolato in `EdisonPortal`;
- (c) integrazione tramite API o stored procedure **fornita e autorizzata** dal proprietario del dato.

### 5.6 Macchina a stati, RBAC, struttura delle cartelle, pagine

Invariati rispetto alla revisione 1:

- stati `Confermato → In preparazione → Pronto per il ritiro → Ritirato`, con `Annullato` come stato finale;
- matrice dei permessi per i cinque ruoli;
- struttura delle cartelle (`app/Domain`, `app/Infrastructure/Legacy`, `resources/js/{design-system,layouts,pages,features}`, `routes/{public,portal,admin}.php`, `tests`, `docs`);
- pagine del sito pubblico, del portale e del gestionale.

---

## 6. Mappa funzionalità ↔ tabelle/API esistenti

Invariata, con **tutte le tabelle reali da rilevare**. Le nuove tabelle, quando necessarie, risiedono in `EdisonPortal`.

---

## 7. Informazioni mancanti (aggiornate)

Legenda: 🔴 bloccante · 🟠 necessaria prima del rilascio · 🟢 utile.

**A. Database SQL Server**
- 🔴 A1. Script del solo schema o `.dacpac`, più i risultati delle query di inventario (Appendice A).
- 🔴 A2. **Versione ed edizione** di SQL Server (Express, Standard, Enterprise, Azure SQL o Managed Instance?) e livello di compatibilità del database.
- 🔴 A3. Dove si trova l'istanza (server aziendale, Azure, hosting) e come la raggiunge il futuro server applicativo. Porta 1433 o istanza nominata? **Non deve essere esposta su Internet.**
- 🔴 A4. Autenticazione disponibile: **SQL Server authentication** e/o **Windows authentication**? Da Linux, la Windows authentication richiede Kerberos: con un'applicazione su Linux è più semplice un login SQL dedicato.
- 🟠 A5. Il DBA può creare il database `EdisonPortal` sulla stessa istanza? In alternativa, uno schema `portal` nel database esistente?
- 🟠 A6. La connessione supporta TLS (`Encrypt=yes`) con un certificato valido?
- 🟠 A7. Policy di backup (full, differenziali, log) e prova di ripristino. Esiste un ambiente di test?

**B. Sistemi proprietari dei dati**
- 🔴 B1. Quale software usa il database (nome, fornitore, versione)? Il contratto consente accessi diretti in lettura?
- 🔴 B2. Quale sistema crea e aggiorna clienti, prodotti, prezzi e giacenze?
- 🟠 B3. Integrazione degli ordini con l'ERP (subito o in futuro)? Esistono API o stored procedure ufficiali del fornitore?

**C/D. Account clienti**
- 🔴 D1. **Il database contiene già account e credenziali dei clienti?**
- 🔴 D2. Identificativo di login, algoritmo di hash (query A.3), gestione di reset e blocchi.
- 🔴 D3. Un cliente può avere più account?
- 🟠 D4. Onboarding dei clienti esistenti tramite invito email.

**E–M.** Invariate rispetto alla revisione 1:

- **E:** regole commerciali — IVA, arrotondamenti, disponibilità, minimi e multipli, sconti, numerazione;
- **F:** sede, orari e ciclo dell'ordine;
- **G:** logo, brand, dati societari, testi, foto;
- **H:** privacy, cookie, retention;
- **I:** infrastruttura, con in più **I1-bis: Linux/Docker o Windows Server/IIS?**;
- **J:** ruoli interni, SSO con Microsoft Entra ID, MFA;
- **K–M:** documenti, notifiche, assistenza, "analisi dei movimenti", volumi.

---

## 8. Rischi (aggiornati per SQL Server)

| # | Rischio | Mitigazione |
|---|---|---|
| R1 | Nessun accesso o schema del database | Kit dell'Appendice A. Nel frattempo, sviluppo delle parti indipendenti |
| R2 | Edizione **Express** (10 GB per database, risorse limitate) | Dimensionamento, valutazione dell'edizione Standard o di un database dedicato |
| R3 | Dati di proprietà di un ERP o software di terzi | Sola lettura, viste dedicate concordate col DBA, API o stored procedure del fornitore |
| R4 | Doppia vendita portale/altri canali | Impegnato in `EdisonPortal` o disponibilità informativa |
| R5 | Credenziali legacy deboli (Membership SHA-1, MD5, in chiaro) | Nuovi account con invito. Nessun tentativo di decifrare |
| R6 | Collation e tipi testo misti (`VARCHAR` con code page, `NVARCHAR`) | Collation allineata, conversioni esplicite nello strato legacy |
| R7 | Blocchi tra portale e gestionale esistente (`READ_COMMITTED_SNAPSHOT` disattivo) | Query brevi e indicizzate, cache del catalogo, eventuale replica in sola lettura (Always On readable secondary) |
| R8 | Versione SQL Server fuori supporto (es. 2012/2014) | Segnalazione al DBA, compatibilità verificata sulla versione reale |
| R9 | Hosting Windows/IIS senza Docker | Procedura di deploy dedicata, driver `sqlsrv` per Windows |
| R10 | Indici univoci con NULL | Indici filtrati (già previsti nel modello) |
| R11 | Logo, contenuti e testi legali mancanti | Placeholder marcati che bloccano il rilascio |
| R12 | Crescita del perimetro | Approvazione per ogni funzione nuova |

---

## 9. Piano di sicurezza — aggiunte per SQL Server

Si aggiungono al piano della revisione 1:

- **Login dedicati a privilegi minimi:** `edison_ro`, `edison_app`, `edison_migrator`. **Mai `sa`**, mai `sysadmin` o `db_owner` per l'applicazione. Permessi concessi per schema o oggetto (`GRANT SELECT ON SCHEMA::…`).
- **Audit append-only:** a `edison_app` si nega UPDATE e DELETE su `audit_logs` (`DENY UPDATE, DELETE ON …`).
- **Connessione cifrata:** `Encrypt=yes`, `TrustServerCertificate=no` in produzione.
- **Query sempre parametrizzate** (PDO e Eloquent). Nessun SQL dinamico con input utente, nemmeno in stored procedure.
- **Segreti di connessione** solo in variabili d'ambiente o secret manager.
- Le funzionalità di cifratura native già attive (TDE, Always Encrypted) vanno rilevate e rispettate.

---

## 10. Piano di test — aggiornamenti

- **Integrazione e funzionali** su **SQL Server reale**:
  - in locale, SQL Server 2025 Developer;
  - in CI, il container ufficiale `mcr.microsoft.com/mssql/server`;
  - per i test definitivi, la stessa versione e lo stesso livello di compatibilità della produzione.
- Copertura specifica:
  - indici univoci filtrati;
  - collation;
  - lock e concorrenza sulla conferma ordine;
  - rollback.

---

## 11. Fasi e tempi

**Invariati** rispetto alla revisione 1 (≈ 17–20 settimane di lavoro per un senior full-stack, esclusi i tempi di attesa dei materiali). Per la Fase 1 vale ora l'analisi SQL Server dell'Appendice A.

---

## Appendice A — Kit di estrazione SQL Server (solo lettura)

Da far eseguire **al DBA di Edison**, preferibilmente su ambiente di test o replica. Nessun comando modifica dati o schema. Se possibile, usare la **Windows authentication** (nessuna password nel comando). **Le password non vanno mai scritte nei comandi né condivise.**

Le stesse query sono nel file eseguibile `docs/database/kit-estrazione-sqlserver.sql`.

### A.1 Script del solo schema

**Opzione 1 — SSMS** (consigliata per semplicità):

1. Tasto destro sul database → *Attività* → *Genera script*.
2. Selezionare tutti gli oggetti.
3. *Avanzate* → *Tipi di dati da includere nello script* = **Solo schema**.
4. Impostare a *True*: indici, chiavi esterne, vincoli CHECK, trigger, autorizzazioni a livello di oggetto, collation.
5. Salvare in un unico file `.sql`.

**Opzione 2 — `sqlpackage`** (file `.dacpac`, solo schema):

```bash
sqlpackage /Action:Extract /SourceConnectionString:"Server=<HOST>;Database=<NOME_DB>;Integrated Security=True;Encrypt=True;" /TargetFile:edison_schema.dacpac /p:ExtractAllTableData=false
```

### A.2 Inventario (viste di catalogo, nessun dato aziendale)

Eseguire nel contesto del database aziendale (`USE <NOME_DB>;`) ed esportare ogni risultato in CSV.

**Permessi richiesti:**

- `VIEW DEFINITION` sul database;
- `VIEW DATABASE STATE` per le dimensioni (su SQL Server 2022+ basta `VIEW DATABASE PERFORMANCE STATE`);
- lettura su `msdb` per i job di Agent.

```sql
-- 1. Istanza e database
SELECT SERVERPROPERTY('ProductVersion')  AS versione,
       SERVERPROPERTY('ProductLevel')    AS livello,
       SERVERPROPERTY('Edition')         AS edizione,
       SERVERPROPERTY('Collation')       AS collation_istanza,
       SERVERPROPERTY('IsIntegratedSecurityOnly') AS solo_windows_auth;

SELECT name, compatibility_level, collation_name, recovery_model_desc,
       is_read_committed_snapshot_on, snapshot_isolation_state_desc,
       is_encrypted AS tde_attivo, is_cdc_enabled
FROM sys.databases
WHERE name = DB_NAME();

-- 2. Tabelle: righe e dimensione
SELECT s.name AS schema_name, t.name AS table_name,
       SUM(CASE WHEN ps.index_id IN (0, 1) THEN ps.row_count ELSE 0 END) AS righe,
       CAST(SUM(ps.reserved_page_count) * 8 / 1024.0 AS DECIMAL(12, 1)) AS spazio_mb,
       t.create_date, t.modify_date, t.temporal_type_desc
FROM sys.tables t
JOIN sys.schemas s ON s.schema_id = t.schema_id
JOIN sys.dm_db_partition_stats ps ON ps.object_id = t.object_id
GROUP BY s.name, t.name, t.create_date, t.modify_date, t.temporal_type_desc
ORDER BY s.name, t.name;

-- 3. Colonne di tabelle e viste (max_length è in byte: per NVARCHAR vale il doppio dei caratteri)
SELECT s.name AS schema_name, o.name AS oggetto, o.type_desc, c.column_id,
       c.name AS colonna, ty.name AS tipo, c.max_length, c.precision, c.scale,
       c.is_nullable, c.is_identity, c.is_computed, c.collation_name,
       dc.definition AS default_definito
FROM sys.columns c
JOIN sys.objects o ON o.object_id = c.object_id AND o.type IN ('U', 'V')
JOIN sys.schemas s ON s.schema_id = o.schema_id
JOIN sys.types ty ON ty.user_type_id = c.user_type_id
LEFT JOIN sys.default_constraints dc ON dc.object_id = c.default_object_id
WHERE o.is_ms_shipped = 0
ORDER BY s.name, o.name, c.column_id;

-- 4. Indici (comprese PK, UNIQUE e indici filtrati)
SELECT s.name AS schema_name, t.name AS table_name, i.name AS indice, i.type_desc,
       i.is_primary_key, i.is_unique, i.is_unique_constraint,
       i.has_filter, i.filter_definition,
       ic.key_ordinal, ic.is_included_column, c.name AS colonna
FROM sys.indexes i
JOIN sys.tables t ON t.object_id = i.object_id
JOIN sys.schemas s ON s.schema_id = t.schema_id
JOIN sys.index_columns ic ON ic.object_id = i.object_id AND ic.index_id = i.index_id
JOIN sys.columns c ON c.object_id = ic.object_id AND c.column_id = ic.column_id
WHERE i.type > 0
ORDER BY s.name, t.name, i.name, ic.key_ordinal;

-- 5. Chiavi esterne
SELECT fk.name AS fk, sp.name AS schema_figlia, tp.name AS tabella_figlia, cp.name AS colonna_figlia,
       sr.name AS schema_padre, tr.name AS tabella_padre, cr.name AS colonna_padre,
       fk.delete_referential_action_desc, fk.update_referential_action_desc,
       fk.is_disabled, fk.is_not_trusted
FROM sys.foreign_keys fk
JOIN sys.foreign_key_columns fkc ON fkc.constraint_object_id = fk.object_id
JOIN sys.tables tp ON tp.object_id = fkc.parent_object_id
JOIN sys.schemas sp ON sp.schema_id = tp.schema_id
JOIN sys.columns cp ON cp.object_id = fkc.parent_object_id AND cp.column_id = fkc.parent_column_id
JOIN sys.tables tr ON tr.object_id = fkc.referenced_object_id
JOIN sys.schemas sr ON sr.schema_id = tr.schema_id
JOIN sys.columns cr ON cr.object_id = fkc.referenced_object_id AND cr.column_id = fkc.referenced_column_id
ORDER BY sp.name, tp.name, fk.name;

-- 6. Vincoli CHECK
SELECT OBJECT_SCHEMA_NAME(parent_object_id) AS schema_name,
       OBJECT_NAME(parent_object_id) AS table_name,
       name AS vincolo, definition, is_disabled
FROM sys.check_constraints
ORDER BY schema_name, table_name;

-- 7. Viste, procedure, funzioni, sinonimi, sequence
SELECT s.name AS schema_name, o.name, o.type_desc, o.create_date, o.modify_date
FROM sys.objects o
JOIN sys.schemas s ON s.schema_id = o.schema_id
WHERE o.type IN ('V', 'P', 'FN', 'IF', 'TF', 'SN', 'SO') AND o.is_ms_shipped = 0
ORDER BY o.type_desc, s.name, o.name;

SELECT name AS sinonimo, base_object_name FROM sys.synonyms;

-- 8. Trigger sulle tabelle
SELECT OBJECT_SCHEMA_NAME(tr.parent_id) AS schema_name, OBJECT_NAME(tr.parent_id) AS table_name,
       tr.name AS trigger_name, te.type_desc AS evento,
       tr.is_instead_of_trigger, tr.is_disabled
FROM sys.triggers tr
JOIN sys.trigger_events te ON te.object_id = tr.object_id
WHERE tr.parent_class = 1
ORDER BY schema_name, table_name;

-- 9. Change Tracking e tabelle tracciate (canali di sincronizzazione già disponibili)
SELECT DB_NAME(database_id) AS database_name, is_auto_cleanup_on, retention_period, retention_period_units_desc
FROM sys.change_tracking_databases;
SELECT OBJECT_SCHEMA_NAME(object_id) AS schema_name, OBJECT_NAME(object_id) AS table_name
FROM sys.change_tracking_tables;

-- 10. Linked server (dati che arrivano da altri sistemi)
SELECT name, product, provider, data_source FROM sys.servers WHERE is_linked = 1;

-- 11. Job di SQL Server Agent (equivalente degli eventi schedulati)
SELECT j.name, j.enabled, j.description, j.date_modified
FROM msdb.dbo.sysjobs j
ORDER BY j.name;

-- 12. Principal, ruoli e permessi (nessuna password o hash)
SELECT dp.name AS principal, dp.type_desc, dp.authentication_type_desc,
       dp.default_schema_name, r.name AS ruolo
FROM sys.database_principals dp
LEFT JOIN sys.database_role_members rm ON rm.member_principal_id = dp.principal_id
LEFT JOIN sys.database_principals r ON r.principal_id = rm.role_principal_id
WHERE dp.type IN ('S', 'U', 'G', 'E', 'X')
ORDER BY dp.name;

SELECT pr.name AS principal, pe.permission_name, pe.state_desc, pe.class_desc,
       CASE pe.class WHEN 1 THEN OBJECT_SCHEMA_NAME(pe.major_id) + '.' + OBJECT_NAME(pe.major_id)
                     WHEN 3 THEN SCHEMA_NAME(pe.major_id)
                     ELSE NULL END AS oggetto
FROM sys.database_permissions pe
JOIN sys.database_principals pr ON pr.principal_id = pe.grantee_principal_id
ORDER BY pr.name, oggetto;
```

### A.3 Solo se esiste una tabella con credenziali (a cura del DBA)

Rivela **solo il tipo di algoritmo**, non le password né gli hash completi.

```sql
-- Colonna testuale (NVARCHAR/VARCHAR)
SELECT LEFT(<colonna_password>, 7) AS prefisso, LEN(<colonna_password>) AS lunghezza, COUNT(*) AS occorrenze
FROM <schema>.<tabella_account>
GROUP BY LEFT(<colonna_password>, 7), LEN(<colonna_password>)
ORDER BY occorrenze DESC;

-- Colonna binaria (VARBINARY, es. HASHBYTES)
SELECT DATALENGTH(<colonna_password>) AS byte, COUNT(*) AS occorrenze
FROM <schema>.<tabella_account>
GROUP BY DATALENGTH(<colonna_password>);
```

**Lettura del risultato:**

| Indizio | Algoritmo probabile | Valutazione |
|---|---|---|
| Prefisso `AQAAAA` | ASP.NET Core Identity v3 (PBKDF2) | Robusto, verificabile anche da PHP |
| `$2y$` / `$2b$` | bcrypt | Accettabile |
| `$argon2` | Argon2 | Robusto |
| Base64 di 28 caratteri con colonna `PasswordSalt` separata | ASP.NET Membership (SHA-1) | **Non sicuro** |
| 16 / 20 / 32 byte binari | MD5 / SHA-1 / SHA-256 (es. `HASHBYTES`) | **Non sicuri** se non salati e iterati |
| 32 / 40 / 64 caratteri esadecimali | MD5 / SHA-1 / SHA-256 | **Non sicuri** |
| Lunghezze variabili senza struttura | Possibile password in chiaro | **Da segnalare subito** |
