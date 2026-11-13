# ADR 0001 — Stack: Laravel + Inertia + React/TypeScript

- **Stato:** approvato dal committente (13/11/2026)
- **Contesto:** si tratta di una piattaforma B2B con tre aree. I requisiti di sicurezza sono elevati: autenticazione, RBAC, audit, CSRF, rate limiting. Il database aziendale è SQL Server. Serve un design system unico per le tre aree.
- **Decisione:**
  - backend in monolite modulare Laravel 13;
  - frontend React 19 con TypeScript strict tramite Inertia.js 3;
  - Tailwind CSS 4.
- **Motivazioni:**
  - sicurezza e funzioni di prodotto integrate e mature;
  - supporto ufficiale di SQL Server (driver Microsoft);
  - un solo frontend React;
  - nessuna API REST da mantenere per il frontend;
  - ampia disponibilità di manutentori.
- **Alternative scartate:**
  - Next.js full-stack: troppi componenti di sicurezza da assemblare;
  - Next.js + NestJS: due servizi da mantenere;
  - Laravel + Filament: interfaccia non condivisa con sito e area clienti;
  - ASP.NET Core: da rivalutare solo se l'IT di Edison impone uno stack esclusivamente Microsoft.
- **Conseguenze:** in produzione servono PHP con `pdo_sqlsrv` e ODBC Driver 18, un worker delle code e Node solo in fase di build.
