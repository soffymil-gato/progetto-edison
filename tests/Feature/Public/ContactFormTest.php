<?php

declare(strict_types=1);

use App\Domain\Contact\ContactRequest;
use App\Domain\Contact\NewContactRequestNotification;
use App\Domain\Settings\SettingsRepository;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

function validContact(array $overrides = []): array
{
    return array_merge([
        'name' => 'Mario Prova',
        'company' => 'Azienda di prova',
        'email' => 'Mario.Prova@example.test',
        'phone' => '+39 000 0000000',
        'subject' => 'Richiesta informazioni',
        'message' => 'Vorrei ricevere informazioni sui prodotti.',
        'privacy' => true,
    ], $overrides);
}

it('stores a valid request without keeping IP address or user agent', function (): void {
    Notification::fake();

    $this->from('/contatti')->post('/contatti', validContact())
        ->assertRedirect('/contatti')
        ->assertSessionHas('success');

    $request = ContactRequest::query()->sole();
    expect($request->email)->toBe('mario.prova@example.test')
        ->and($request->status->value)->toBe('new')
        ->and($request->privacy_accepted_at)->not->toBeNull()
        ->and(array_keys($request->getAttributes()))->not->toContain('ip_address');
});

it('validates the form on the server with Italian messages', function (): void {
    $this->from('/contatti')->post('/contatti', validContact(['email' => 'non-valida', 'privacy' => false, 'message' => '']))
        ->assertSessionHasErrors([
            'email' => 'Il campo email deve essere un indirizzo email valido.',
            'privacy' => 'Per inviare la richiesta è necessario prendere visione dell\'informativa privacy.',
            'message' => 'Il campo messaggio è obbligatorio.',
        ]);

    expect(ContactRequest::query()->count())->toBe(0);
});

it('silently discards submissions that fill the honeypot', function (): void {
    $this->from('/contatti')->post('/contatti', validContact(['website' => 'http://spam.example']))
        ->assertSessionHas('success');

    expect(ContactRequest::query()->count())->toBe(0);
});

it('notifies the configured recipient without copying the message in the email', function (): void {
    Notification::fake();
    app(SettingsRepository::class)->update('contact_form', ['recipient_email' => 'richieste@example.test'], $this->superAdmin());

    $this->post('/contatti', validContact());

    Notification::assertSentTo(new AnonymousNotifiable, NewContactRequestNotification::class, function (NewContactRequestNotification $n, array $channels, AnonymousNotifiable $notifiable): bool {
        $mail = $n->toMail($notifiable);

        return $notifiable->routes['mail'] === 'richieste@example.test'
            && ! str_contains(implode(' ', $mail->introLines), 'Vorrei ricevere informazioni');
    });
});

it('keeps the request in the admin panel when no recipient is configured', function (): void {
    Notification::fake();

    $this->post('/contatti', validContact());

    Notification::assertNothingSent();
    expect(ContactRequest::query()->count())->toBe(1);
});

it('rate limits the form per IP address', function (): void {
    Notification::fake();

    foreach (range(1, 5) as $i) {
        $this->post('/contatti', validContact(['subject' => "Richiesta {$i}"]))->assertRedirect();
    }

    $this->post('/contatti', validContact())->assertStatus(429);
    expect(ContactRequest::query()->count())->toBe(5);
});
