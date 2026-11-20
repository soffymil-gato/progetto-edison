# Permessi SQL Server — proposta per staging e produzione

> **Proposta da validare con il DBA di Edison. Nulla di questo documento è stato eseguito su sistemi di Edison.**
> I nomi dei database aziendali sono segnaposto (`<DB_AZIENDALE>`).
> Le password **non** vanno scritte in questo file né in script versionati: si impostano al momento della creazione tramite un canale sicuro e si custodiscono nel secret manager.

## Principi

| Login | Uso | Permessi |
|---|---|---|
| `edison_app` | Applicazione in esecuzione (web e worker) | DML sul database `EdisonPortal`. **UPDATE e DELETE negati** su `audit_logs` |
| `edison_migrator` | Solo durante il deploy (migrazioni) | DDL su `EdisonPortal` |
| `edison_ro` | Lettura del database aziendale | **Solo SELECT** sugli oggetti necessari, o su uno schema di viste dedicate |

**Vincoli comuni:**

- mai `sa`, mai `sysadmin`, mai `db_owner` per l'applicazione;
- connessioni con `Encrypt=yes` e certificato valido (`DB_TRUST_SERVER_CERTIFICATE=false` in produzione).

## Script indicativo

Il seguente script va eseguito dal DBA, dopo backup e in finestra concordata. Prima della creazione dei login, sostituire `<PASSWORD_DA_SECRET_MANAGER>` digitandola in modo interattivo.

```sql
-- 1. Database applicativo (collation da allineare a quella del database aziendale)
CREATE DATABASE EdisonPortal;
ALTER DATABASE EdisonPortal SET READ_COMMITTED_SNAPSHOT ON WITH ROLLBACK IMMEDIATE;
GO

-- 2. Login
CREATE LOGIN edison_app      WITH PASSWORD = '<PASSWORD_DA_SECRET_MANAGER>', CHECK_POLICY = ON;
CREATE LOGIN edison_migrator WITH PASSWORD = '<PASSWORD_DA_SECRET_MANAGER>', CHECK_POLICY = ON;
CREATE LOGIN edison_ro       WITH PASSWORD = '<PASSWORD_DA_SECRET_MANAGER>', CHECK_POLICY = ON;
GO

-- 3. Database applicativo: utente per le migrazioni
USE EdisonPortal;
CREATE USER edison_migrator FOR LOGIN edison_migrator;
ALTER ROLE db_ddladmin ADD MEMBER edison_migrator;
ALTER ROLE db_datareader ADD MEMBER edison_migrator;
ALTER ROLE db_datawriter ADD MEMBER edison_migrator;
GO

-- 4. Database applicativo: utente dell'applicazione (solo dati, nessuna DDL)
CREATE USER edison_app FOR LOGIN edison_app;
GRANT SELECT, INSERT, UPDATE, DELETE ON SCHEMA::dbo TO edison_app;
-- Il registro attività è in sola aggiunta anche a livello di permessi
-- (oltre al trigger audit_logs_append_only creato dalla migrazione).
DENY UPDATE, DELETE ON dbo.audit_logs TO edison_app;
GO

-- 5. Database aziendale: sola lettura, solo sugli oggetti approvati nell'analisi
USE [<DB_AZIENDALE>];
CREATE USER edison_ro FOR LOGIN edison_ro;
-- Preferibile: uno schema di viste dedicate, es. GRANT SELECT ON SCHEMA::web TO edison_ro;
-- In alternativa, oggetto per oggetto:
-- GRANT SELECT ON dbo.<tabella_approvata> TO edison_ro;
GO
```

## Note

- La tabella `audit_logs` è protetta in tre modi:
  - il modello applicativo rifiuta modifiche e cancellazioni;
  - il trigger `INSTEAD OF UPDATE, DELETE` rifiuta le istruzioni dirette;
  - il `DENY` impedisce le modifiche anche a livello di permessi.

  Un'eventuale pulizia per conservazione (retention) va eseguita dal DBA con una procedura documentata.
- La connessione `legacy` (database aziendale) **non è usata** da nessuna funzione finché l'analisi dello schema non è consegnata e approvata.
