# ADR 0002 — Database applicativo dedicato su SQL Server

- **Stato:** approvato in linea di principio, da confermare dopo l'analisi del database aziendale.
- **Contesto:** il database aziendale SQL Server appartiene a software esistenti e non va alterato senza autorizzazione.
- **Decisione:**
  - la piattaforma scrive **solo** nel database `EdisonPortal`, sulla stessa istanza SQL Server;
  - il database aziendale si legge con un login di sola lettura (connessione `legacy`), tramite un unico strato di adapter;
  - nessuna foreign key verso il database aziendale: i riferimenti sono logici (es. `portal_accounts.customer_ref`).
- **Dettagli SQL Server:**
  - `READ_COMMITTED_SNAPSHOT ON`;
  - testi in `NVARCHAR`, date in `DATETIME2` UTC, importi in `DECIMAL`;
  - indici univoci filtrati per le colonne opzionali (SQL Server ammette un solo NULL in un indice univoco);
  - `PDO::SQLSRV_ATTR_FETCHES_NUMERIC_TYPE` attivo e cast `integer` sulle colonne di riferimento `BIGINT`.
- **Alternativa:** schema `portal` nel database aziendale, se il DBA preferisce un unico database.
