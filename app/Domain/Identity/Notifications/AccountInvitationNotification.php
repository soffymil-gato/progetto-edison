<?php

declare(strict_types=1);

namespace App\Domain\Identity\Notifications;

use App\Domain\Identity\Enums\AccountType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class AccountInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        #[\SensitiveParameter] private readonly string $token,
        private readonly AccountType $type,
    ) {
        $this->afterCommit();
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $area = $this->type->area();
        $url = route($area->routeName('invitation.show'), ['token' => $this->token]);
        $hours = (int) config('edison.auth.invitation_ttl_hours');

        return (new MailMessage)
            ->subject($this->type->value === 'staff' ? 'Accesso al gestionale Edison' : 'Accesso all\'area clienti Edison')
            ->greeting('Benvenuto')
            ->line($this->type->value === 'staff'
                ? 'È stato creato per te un account per il gestionale.'
                : 'È stato creato per te un account per l\'area clienti riservata.')
            ->line('Per attivarlo scegli la tua password personale dal link seguente.')
            ->action('Attiva l\'account', $url)
            ->line("Il link è personale e scade tra {$hours} ore. Se non attendevi questa email puoi ignorarla.");
    }
}
