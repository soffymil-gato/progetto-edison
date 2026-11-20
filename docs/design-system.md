# Design system

Fonte unica dei token: `resources/css/app.css` (blocco `@theme` di Tailwind CSS 4). I componenti condivisi sono in `resources/js/design-system/`.

## Token

| Gruppo | Token | Uso |
|---|---|---|
| Blu | `brand-50` … `brand-950`, `ink` | celeste → azzurro → blu elettrico (`500`, solo decorativo) → blu istituzionale (`600–800`) → blu profondo (`950`, `ink`) |
| Accento energia | `spark-300/400/500` | Solo elementi decorativi su fondo scuro, mai testo piccolo su chiaro |
| Superfici | `canvas`, `surface`, `line`, `line-strong` | Sfondi applicativi, card, bordi |
| Testo | `fg`, `fg-muted`, `fg-subtle` | Contrasto ≥ 4,5:1 su bianco e su `canvas` (verificato con axe) |
| Stati | `success-*`, `warning-*`, `danger-*` | Sempre accompagnati da icona o etichetta, mai dal solo colore |
| Tipografia | `font-display` (Sora), `font-sans` (Inter) | Font self-hosted, nessuna richiesta a terzi |
| Forme | `radius-control`, `radius-card`, `radius-panel` | Controlli, card, pannelli |
| Ombre | `shadow-card`, `shadow-elevated`, `shadow-glow` | Ombre tinte di blu profondo |
| Movimento | `animate-fade-up`, `animate-fade-in`, `ease-soft` | Disattivati con `prefers-reduced-motion` |

## Componenti

- **Azioni:** `Button`, `ButtonLink`, `ConfirmDialog`.
- **Moduli:** `Field` (etichetta, aiuto ed errore collegati con ARIA), `Input`, `PasswordInput` (mostra/nascondi), `Textarea`, `Select`, `Checkbox`.
- **Contenuti:** `Card`, `CardHeader`, `CardBody`, `Badge`, `AccountStatusBadge`, `Alert`, `DescriptionList`, `PageHeader`, `EmptyState`.
- **Dati:** `DataTable`, `Pagination`, `Skeleton`, `TableSkeleton`.
  - `DataTable` gestisce ordinamento lato server, skeleton durante il caricamento e card su smartphone, con link accessibili al dettaglio.
- **Feedback:** `Toaster`, per i messaggi del server.
- **Marchio e segnaposto:**
  - `BrandMark` mostra il logo ufficiale, oppure un segnaposto dichiarato finché il logo non è fornito;
  - `Txt`, `MultilineTxt` e `PlaceholderChip` rendono sempre visibili i contenuti «da fornire».

## Layout

- `PublicLayout`: header sticky che si compatta allo scroll, menu mobile accessibile, footer completo.
- `AuthLayout`: pannello del marchio con visual energetico originale (`EnergyField`) e modulo.
- `AppShell`: sidebar scura per il gestionale; navigazione orizzontale chiara per l'area clienti.

## Accessibilità

Obiettivo WCAG 2.2 AA. I test end-to-end eseguono axe su tutte le pagine pubbliche, sulla dashboard del gestionale, sull'elenco utenti e sulla dashboard cliente, su desktop e smartphone. Falliscono a ogni violazione seria o critica.
