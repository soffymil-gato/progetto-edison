/*
  Progetto Edison - Kit di estrazione SQL Server (SOLA LETTURA)
  -------------------------------------------------------------
  Da eseguire a cura del DBA di Edison, preferibilmente su ambiente di test o replica.
  Nessuna istruzione modifica dati o schema: si interrogano solo viste di catalogo (sys.*)
  e, per i job di Agent, msdb.

  Istruzioni:
    1. Sostituire <NOME_DB> con il nome del database aziendale ed eseguire nel suo contesto.
    2. Esportare ogni risultato in CSV (SSMS: "Risultati in file" oppure sqlcmd -s "," -W).
    3. Permessi necessari: VIEW DEFINITION sul database, VIEW DATABASE STATE
       (o VIEW DATABASE PERFORMANCE STATE su SQL Server 2022+), lettura su msdb per i job.
    4. Non inserire password nei comandi: usare preferibilmente la Windows authentication.

  Riferimento: docs/analisi/report-analisi-preliminare.md, Appendice A.
*/

USE [<NOME_DB>];
GO

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
GO

/* ------------------------------------------------------------------------
   A.3 - SOLO se esiste una tabella con credenziali. Sostituire i segnaposto
   <schema>, <tabella_account>, <colonna_password>. La query restituisce solo
   prefisso e lunghezza (tipo di algoritmo), mai l'hash completo.
   Le istruzioni sono commentate per evitare esecuzioni involontarie.
------------------------------------------------------------------------ */
/*
-- Colonna testuale (NVARCHAR/VARCHAR)
SELECT LEFT(<colonna_password>, 7) AS prefisso, LEN(<colonna_password>) AS lunghezza, COUNT(*) AS occorrenze
FROM <schema>.<tabella_account>
GROUP BY LEFT(<colonna_password>, 7), LEN(<colonna_password>)
ORDER BY occorrenze DESC;

-- Colonna binaria (VARBINARY, es. HASHBYTES)
SELECT DATALENGTH(<colonna_password>) AS byte, COUNT(*) AS occorrenze
FROM <schema>.<tabella_account>
GROUP BY DATALENGTH(<colonna_password>);
*/
