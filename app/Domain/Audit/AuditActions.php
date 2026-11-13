<?php

declare(strict_types=1);

namespace App\Domain\Audit;

/**
 * Human-readable labels of the audited actions.
 */
final class AuditActions
{
    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'auth.login.succeeded' => 'Accesso riuscito',
            'auth.login.failed' => 'Accesso non riuscito',
            'auth.login.throttled' => 'Accesso bloccato per troppi tentativi',
            'auth.login.blocked_inactive' => 'Accesso negato: account non attivo',
            'auth.logout' => 'Uscita',
            'auth.password_reset.completed' => 'Password reimpostata tramite recupero',
            'account.password_changed' => 'Password modificata dal profilo',
            'account.invitation.sent' => 'Invito inviato',
            'account.invitation.accepted' => 'Invito accettato, account attivato',
            'staff_user.created' => 'Utente interno creato',
            'staff_user.updated' => 'Utente interno modificato',
            'staff_user.suspended' => 'Utente interno sospeso',
            'staff_user.reactivated' => 'Utente interno riattivato',
            'staff_user.password_reset_sent' => 'Reset password inviato a un utente',
            'role.created' => 'Ruolo creato',
            'role.permissions_updated' => 'Permessi del ruolo modificati',
            'role.deleted' => 'Ruolo eliminato',
            'settings.updated' => 'Impostazioni modificate',
            'contact_request.handled' => 'Richiesta di contatto gestita',
            'portal_account.created' => 'Account cliente creato',
        ];
    }

    public static function label(string $action): string
    {
        return self::labels()[$action] ?? $action;
    }
}
