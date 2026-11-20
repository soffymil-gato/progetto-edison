# Anteprima su Render (gratuita)

L'anteprima serve a mostrare la piattaforma e a lavorarci sopra **senza il database di Edison**. In anteprima:

- usa un database **SQLite** interno al container, con **dati dimostrativi** sintetici;
- è marcata in ogni pagina con l'etichetta «Anteprima — dati dimostrativi»;
- **non è indicizzata** dai motori di ricerca (`robots.txt` `Disallow: /` e `X-Robots-Tag: noindex`);
- le email non vengono inviate, ma scritte nel log del servizio (`MAIL_MAILER=log`).

Staging e produzione usano sempre SQL Server: vedi [`deployment.md`](deployment.md).

## Limiti del piano gratuito di Render

- Dopo 15 minuti senza visite il servizio si spegne. Il primo accesso successivo impiega circa un minuto.
- Il disco non è persistente: **a ogni riavvio o nuovo deploy i dati demo ripartono da zero**. Utenti creati, impostazioni compilate e richieste di contatto non vengono conservati.

## Prima configurazione (una volta sola)

1. Crea un account su [render.com](https://render.com) e collega il tuo account GitHub. Autorizza l'accesso al repository `progetto-edison`.
2. Nel pannello di Render scegli **New → Blueprint** e seleziona il repository. Render legge il file `render.yaml`, che pubblica il branch `claude/inspiring-ritchie-sav3g6`.
3. Render chiede il valore di **`PREVIEW_PASSWORD`**:
   - è la password degli account demo;
   - scegline una di almeno 12 caratteri;
   - condividila solo con chi deve provare l'anteprima;
   - non scriverla nel repository.

   `APP_KEY` viene generata automaticamente da Render.
4. Conferma con **Apply**. Il primo build richiede alcuni minuti. Al termine l'anteprima è raggiungibile all'indirizzo `https://edison-anteprima.onrender.com`, o simile: il nome esatto è indicato nel pannello.

Da quel momento **ogni push sul branch aggiorna automaticamente l'anteprima**.

## Account dimostrativi

Usano tutti la password `PREVIEW_PASSWORD`.

| Area | Indirizzo | Credenziale |
|---|---|---|
| Gestionale (Super Admin) | `/gestionale` | `anteprima.admin@example.test` |
| Gestionale (Operatore, senza permessi aggiuntivi) | `/gestionale` | `anteprima.operatore@example.test` |
| Area clienti | `/area-clienti` | nome utente `cliente.demo` (oppure `anteprima.cliente@example.test`) |

Gli account sono creati da `Database\Seeders\PreviewSeeder` a ogni avvio. Il seeder funziona solo con `EDISON_PREVIEW=true` e si rifiuta di girare in produzione.

## Come è costruita

- **Immagine:** `docker/preview/Dockerfile`, build multi-stadio:
  - asset compilati con Vite;
  - PHP 8.3 con Apache;
  - dipendenze Composer senza quelle di sviluppo.
- **Avvio:** `docker/preview/entrypoint.sh` esegue nell'ordine:
  - migrazioni;
  - account demo;
  - cache di configurazione, rotte e viste;
  - Apache sulla porta indicata da Render.
- **Verifica:** la CI costruisce l'immagine a ogni push, la avvia e controlla sito, pagine di accesso e login demo (job «Immagine Docker dell'anteprima»).
