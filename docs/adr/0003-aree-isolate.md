# ADR 0003 — Tre aree isolate nella stessa applicazione

- **Stato:** attuato.
- **Decisione:** ogni area (sito, area clienti, gestionale) ha:
  - un gruppo di middleware dedicato;
  - un cookie di sessione dedicato (nome e percorso, oppure sottodominio in produzione);
  - una tabella di sessione propria;
  - un cookie XSRF proprio;
  - una guard di autenticazione propria (`customer`, `staff`);
  - un bundle JavaScript distinto: il codice del gestionale non viene mai scaricato da sito o clienti.
- **Motivazioni:** una sessione cliente non può mai essere valida nel gestionale, la chiusura delle sessioni può riguardare una sola area, e la superficie esposta al pubblico si riduce.
- **Note tecniche:**
  - `SetArea` configura la sessione **prima** di `StartSession` e ricollega i servizi che conservano il riferimento allo store (redirector);
  - la scadenza della sessione per inattività è per area; la scadenza assoluta di 12 ore è imposta da `EnforceSessionPolicy`.
