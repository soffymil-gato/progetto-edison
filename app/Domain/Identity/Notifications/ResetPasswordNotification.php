<?php

declare(strict_types=1);

namespace App\Domain\Identity\Notifications;

use App\Domain\Identity\Enums\AccountType;
use App\Domain\Identity\Models\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ResetPasswordNotification extends Notification implements ShouldQueue
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
        $email = $notifiable instanceof Account ? $notifiable->email : '';
        $url = route($this->type->area()->routeName('password.reset'), ['token' => $this->token, 'email' => $email]);
        $minutes = (int) config('auth.passwords.'.$this->type->passwordBroker().'.expire');

        return (new MailMessage)
            ->subject('Reimpostazione della password')
            ->line('Abbiamo ricevuto una richiesta di reimpostazione della password del tuo account.')
            ->action('Reimposta la password', $url)
            ->line("Il link scade tra {$minutes} minuti.")
            ->line('Se non hai richiesto la reimpostazione, ignora questa email: la password attuale resta valida.');
    }
}
