<?php

declare(strict_types=1);

namespace App\Domain\Settings;

/**
 * Definition of the administrative settings. Values are empty until Edison
 * supplies the real data: nothing here is pre-filled or invented.
 *
 * "release" marks the fields that must be filled before going live
 * (checked by `php artisan edison:release-check`).
 */
final class SettingsSchema
{
    /**
     * @return array<string, array{
     *     label: string,
     *     description: string,
     *     fields: array<string, array{label: string, type: 'text'|'textarea'|'email'|'tel', max: int, release: bool, help?: string}>
     * }>
     */
    public static function groups(): array
    {
        return [
            'company' => [
                'label' => 'Dati aziendali',
                'description' => 'Ragione sociale, sede e contatti mostrati nel sito pubblico e nelle comunicazioni.',
                'fields' => [
                    'legal_name' => ['label' => 'Ragione sociale', 'type' => 'text', 'max' => 160, 'release' => true],
                    'short_description' => ['label' => 'Descrizione breve', 'type' => 'textarea', 'max' => 400, 'release' => true, 'help' => 'Mostrata nel footer del sito.'],
                    'registered_office' => ['label' => 'Sede legale (indirizzo completo)', 'type' => 'textarea', 'max' => 300, 'release' => true],
                    'vat_number' => ['label' => 'Partita IVA', 'type' => 'text', 'max' => 20, 'release' => true],
                    'tax_code' => ['label' => 'Codice fiscale', 'type' => 'text', 'max' => 20, 'release' => false],
                    'rea' => ['label' => 'Numero REA', 'type' => 'text', 'max' => 40, 'release' => false, 'help' => 'Ufficio del Registro delle imprese e numero di iscrizione, se applicabile.'],
                    'share_capital' => ['label' => 'Capitale sociale', 'type' => 'text', 'max' => 60, 'release' => false, 'help' => 'Obbligatorio nel sito per le società di capitali.'],
                    'phone' => ['label' => 'Telefono', 'type' => 'tel', 'max' => 40, 'release' => true],
                    'email' => ['label' => 'Email', 'type' => 'email', 'max' => 254, 'release' => true],
                    'pec' => ['label' => 'PEC', 'type' => 'email', 'max' => 254, 'release' => false],
                    'office_hours' => ['label' => 'Orari degli uffici', 'type' => 'textarea', 'max' => 300, 'release' => true],
                ],
            ],
            'pickup' => [
                'label' => 'Sede e orari di ritiro',
                'description' => 'Indirizzo e orari comunicati ai clienti per il ritiro degli ordini presso la sede.',
                'fields' => [
                    'address' => ['label' => 'Indirizzo della sede di ritiro', 'type' => 'textarea', 'max' => 300, 'release' => true],
                    'hours' => ['label' => 'Orari di ritiro', 'type' => 'textarea', 'max' => 400, 'release' => true],
                    'notes' => ['label' => 'Indicazioni per il ritiro', 'type' => 'textarea', 'max' => 600, 'release' => false, 'help' => 'Ad esempio documenti da presentare o accesso al magazzino.'],
                ],
            ],
            'contact_form' => [
                'label' => 'Modulo contatti',
                'description' => 'Destinatario delle richieste inviate dal modulo del sito.',
                'fields' => [
                    'recipient_email' => ['label' => 'Email destinataria', 'type' => 'email', 'max' => 254, 'release' => true, 'help' => 'Ogni nuova richiesta viene registrata nel gestionale e notificata a questo indirizzo.'],
                ],
            ],
        ];
    }

    public static function hasGroup(string $group): bool
    {
        return array_key_exists($group, self::groups());
    }

    /** @return array<string, list<string>> */
    public static function validationRules(string $group): array
    {
        $rules = [];
        foreach (self::groups()[$group]['fields'] as $key => $field) {
            $fieldRules = ['nullable', 'string', 'max:'.$field['max']];
            if ($field['type'] === 'email') {
                $fieldRules[] = 'email:rfc';
            }
            $rules[$key] = $fieldRules;
        }

        return $rules;
    }
}
