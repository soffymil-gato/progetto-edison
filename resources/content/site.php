<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Contenuti editoriali del sito pubblico
|--------------------------------------------------------------------------
|
| REGOLA: nessun dato aziendale inventato. I testi presenti derivano solo
| dalla specifica approvata (es. "prodotti e servizi per il settore
| energetico", funzionamento dell'area clienti). Tutto il resto è marcato
| "[DA FORNIRE: …]" e blocca il rilascio (php artisan edison:release-check)
| finché Edison non fornisce i testi definitivi.
|
| Dati di contatto, sede, orari e dati societari NON stanno qui: si gestiscono
| dal gestionale (Impostazioni).
|
*/

$todo = static fn (string $what): string => "[DA FORNIRE: {$what}]";

$values = [
    'affidabilita' => 'Affidabilità',
    'competenza' => 'Competenza',
    'innovazione' => 'Innovazione',
    'qualita' => 'Qualità',
    'assistenza' => 'Assistenza',
];

return [

    'meta' => [
        'home' => [
            'title' => 'Prodotti e servizi per il settore energetico',
            'description' => $todo('descrizione per i motori di ricerca della home, massimo 160 caratteri'),
        ],
        'azienda' => [
            'title' => 'Azienda',
            'description' => $todo('descrizione per i motori di ricerca della pagina Azienda'),
        ],
        'prodotti' => [
            'title' => 'Prodotti',
            'description' => $todo('descrizione per i motori di ricerca della pagina Prodotti'),
        ],
        'servizi' => [
            'title' => 'Servizi',
            'description' => $todo('descrizione per i motori di ricerca della pagina Servizi'),
        ],
        'contatti' => [
            'title' => 'Contatti',
            'description' => 'Contatti, sede e modulo di richiesta informazioni.',
        ],
        'privacy' => [
            'title' => 'Privacy Policy',
            'description' => 'Informativa sul trattamento dei dati personali.',
        ],
        'cookie' => [
            'title' => 'Cookie Policy',
            'description' => 'Informativa sui cookie utilizzati dal sito.',
        ],
    ],

    'home' => [
        'hero' => [
            'eyebrow' => 'Settore energetico',
            'title' => 'Prodotti e servizi per il settore energetico',
            'subtitle' => $todo('presentazione sintetica dell\'azienda in una o due frasi'),
        ],
        'about' => [
            'title' => 'Chi siamo',
            'intro' => $todo('presentazione dell\'azienda: esperienza e competenze'),
        ],
        'products_intro' => $todo('introduzione alle principali categorie di prodotto'),
        'services_intro' => $todo('introduzione ai servizi offerti'),
        'why' => [
            'title' => 'Perché Edison',
            'items' => array_map(
                static fn (string $label): array => ['title' => $label, 'text' => $todo("descrizione del valore «{$label}»")],
                array_values($values),
            ),
        ],
        'portal' => [
            'title' => 'Area clienti riservata',
            'text' => 'I clienti autorizzati consultano il catalogo e il listino, inviano ordini confermati e li ritirano presso la sede aziendale.',
            'points' => [
                'Catalogo e listino unici per tutti i clienti',
                'Ordine confermato al momento dell\'invio',
                'Stato dell\'ordine sempre consultabile',
                'Ritiro presso la sede aziendale',
            ],
        ],
    ],

    'azienda' => [
        'title' => 'Azienda',
        'intro' => $todo('introduzione alla pagina Azienda'),
        'sections' => [
            ['key' => 'storia', 'title' => 'Storia', 'text' => $todo('storia dell\'azienda')],
            ['key' => 'mission', 'title' => 'Mission', 'text' => $todo('mission')],
            ['key' => 'vision', 'title' => 'Vision', 'text' => $todo('vision')],
            ['key' => 'esperienza', 'title' => 'Esperienza e competenze', 'text' => $todo('esperienza e competenze')],
        ],
        'values_title' => 'I nostri valori',
        'values' => array_map(
            static fn (string $label): array => ['title' => $label, 'text' => $todo("descrizione del valore «{$label}»")],
            array_values($values),
        ),
    ],

    'prodotti' => [
        'title' => 'Prodotti',
        'intro' => $todo('introduzione al catalogo prodotti'),
        // Categorie principali da presentare nel sito (editoriali). Il catalogo
        // completo con prezzi resta nell'area clienti e verrà letto dal database aziendale.
        'categories' => [
            ['name' => $todo('nome della categoria di prodotto n. 1'), 'description' => $todo('descrizione della categoria n. 1')],
            ['name' => $todo('nome della categoria di prodotto n. 2'), 'description' => $todo('descrizione della categoria n. 2')],
            ['name' => $todo('nome della categoria di prodotto n. 3'), 'description' => $todo('descrizione della categoria n. 3')],
        ],
        'catalog_note' => 'Catalogo completo, prezzi e disponibilità sono riservati ai clienti autorizzati nell\'area clienti.',
    ],

    'servizi' => [
        'title' => 'Servizi',
        'intro' => $todo('introduzione ai servizi'),
        'items' => [
            ['name' => $todo('nome del servizio n. 1'), 'description' => $todo('descrizione, vantaggi e dettagli del servizio n. 1')],
            ['name' => $todo('nome del servizio n. 2'), 'description' => $todo('descrizione, vantaggi e dettagli del servizio n. 2')],
            ['name' => $todo('nome del servizio n. 3'), 'description' => $todo('descrizione, vantaggi e dettagli del servizio n. 3')],
        ],
        'ordering' => [
            'title' => 'Ordini online con ritiro in sede',
            'text' => 'Dall\'area clienti i clienti autorizzati inviano ordini già confermati e ritirano la merce presso la sede aziendale, negli orari indicati.',
        ],
    ],

    'contatti' => [
        'title' => 'Contatti',
        'intro' => 'Per informazioni su prodotti e servizi scrivici tramite il modulo: ti risponderemo il prima possibile.',
    ],

    'legal' => [
        'privacy' => [
            'title' => 'Privacy Policy',
            'notice' => 'Testo da validare a cura del titolare del trattamento o del consulente incaricato.',
            'sections' => [
                ['title' => 'Titolare del trattamento', 'text' => $todo('identità e dati di contatto del titolare ed eventuale DPO')],
                ['title' => 'Dati trattati e finalità', 'text' => $todo('categorie di dati e finalità del trattamento (modulo contatti, area clienti, ordini)')],
                ['title' => 'Base giuridica', 'text' => $todo('base giuridica per ciascuna finalità')],
                ['title' => 'Conservazione', 'text' => $todo('tempi di conservazione per ciascuna categoria di dati')],
                ['title' => 'Destinatari', 'text' => $todo('responsabili del trattamento e categorie di destinatari')],
                ['title' => 'Diritti dell\'interessato', 'text' => $todo('diritti degli interessati e modalità di esercizio')],
            ],
        ],
        'cookie' => [
            'title' => 'Cookie Policy',
            'notice' => 'Testo da validare a cura del titolare del trattamento o del consulente incaricato.',
            'intro' => $todo('testo introduttivo della cookie policy'),
            // Elenco tecnico verificato: sono i soli cookie impostati dalla piattaforma.
            // Nessun cookie di profilazione o di analisi statistica è utilizzato.
            'technical_cookies' => [
                ['name' => 'edison_public_session', 'purpose' => 'Sessione tecnica del sito (es. invio del modulo contatti)', 'duration' => 'Sessione / 2 ore di inattività'],
                ['name' => 'XSRF-TOKEN', 'purpose' => 'Protezione dei moduli da richieste contraffatte (CSRF)', 'duration' => 'Come la sessione'],
                ['name' => 'edison_portal_session', 'purpose' => 'Autenticazione nell\'area clienti', 'duration' => 'Fino al logout o a 60 minuti di inattività'],
                ['name' => 'XSRF-TOKEN-PORTAL', 'purpose' => 'Protezione CSRF dell\'area clienti', 'duration' => 'Come la sessione'],
                ['name' => 'edison_admin_session', 'purpose' => 'Autenticazione nel gestionale (solo personale interno)', 'duration' => 'Fino al logout o a 30 minuti di inattività'],
                ['name' => 'XSRF-TOKEN-ADMIN', 'purpose' => 'Protezione CSRF del gestionale', 'duration' => 'Come la sessione'],
            ],
        ],
    ],

];
