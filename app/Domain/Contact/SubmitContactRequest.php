<?php

declare(strict_types=1);

namespace App\Domain\Contact;

use App\Domain\Settings\SettingsRepository;
use Illuminate\Support\Facades\Notification;

/**
 * Stores a request from the public contact form and notifies the configured
 * recipient (if any). The request is always kept in the admin panel, so it is
 * never lost when the recipient is not configured yet.
 */
final class SubmitContactRequest
{
    public function __construct(private readonly SettingsRepository $settings) {}

    /** @param array{name: string, company?: string|null, email: string, phone?: string|null, subject: string, message: string} $data */
    public function handle(array $data): ContactRequest
    {
        $request = ContactRequest::query()->create([
            'name' => $data['name'],
            'company' => $data['company'] ?? null,
            'email' => mb_strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'privacy_accepted_at' => now(),
        ]);

        $recipient = $this->settings->value('contact_form', 'recipient_email');
        if ($recipient !== null) {
            Notification::route('mail', $recipient)->notify(new NewContactRequestNotification($request));
        }

        return $request;
    }
}
