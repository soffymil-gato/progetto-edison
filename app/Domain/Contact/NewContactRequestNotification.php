<?php

declare(strict_types=1);

namespace App\Domain\Contact;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification to the internal recipient. The message body is NOT copied in
 * the email (data minimisation): the email links to the admin panel.
 */
final class NewContactRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly ContactRequest $request)
    {
        $this->afterCommit();
    }

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nuova richiesta di contatto dal sito')
            ->line('È arrivata una nuova richiesta dal modulo contatti del sito.')
            ->line('Oggetto: '.$this->request->subject)
            ->action('Apri nel gestionale', route('admin.contact-requests.show', $this->request))
            ->line('Per riservatezza il contenuto è consultabile solo dal gestionale.');
    }
}
