<?php

declare(strict_types=1);

use App\Domain\Audit\AuditLog;
use App\Domain\Audit\AuditLogger;
use App\Domain\Contact\ContactRequest;
use App\Domain\Settings\SettingsRepository;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('updates company settings, audits old and new values and shows them on the public site', function (): void {
    $admin = $this->superAdmin();

    $this->actingAsStaff($admin)->put('/gestionale/impostazioni/company', [
        'legal_name' => 'Società di Prova S.r.l.',
        'phone' => '+39 000 1111111',
        'email' => 'info@example.test',
    ])->assertSessionHasNoErrors();

    expect(app(SettingsRepository::class)->value('company', 'legal_name'))->toBe('Società di Prova S.r.l.');

    $log = AuditLog::query()->where('action', 'settings.updated')->sole();
    expect($log->actor_id)->toBe($admin->id)
        ->and(collect($log->context['changes'])->pluck('field')->all())->toEqualCanonicalizing(['legal_name', 'phone', 'email']);

    $this->get('/contatti')->assertInertia(fn ($page) => $page->where('company.phone', '+39 000 1111111'));
});

it('validates settings on the server', function (): void {
    $this->actingAsStaff($this->superAdmin())
        ->put('/gestionale/impostazioni/contact_form', ['recipient_email' => 'non-una-email'])
        ->assertSessionHasErrors('recipient_email');

    $this->actingAsStaff($this->superAdmin())->put('/gestionale/impostazioni/inesistente', [])->assertNotFound();
});

it('keeps the audit log append-only in the application and in the database', function (): void {
    $log = app(AuditLogger::class)->record('test.event');

    expect(fn () => $log->update(['action' => 'manomesso']))->toThrow(LogicException::class);
    expect(fn () => $log->delete())->toThrow(LogicException::class);
    // Even a direct SQL statement is rejected by the SQL Server trigger.
    expect(fn () => DB::table('audit_logs')->where('id', $log->id)->update(['action' => 'manomesso']))->toThrow(QueryException::class, 'append-only');
    expect(fn () => DB::table('audit_logs')->where('id', $log->id)->delete())->toThrow(QueryException::class, 'append-only');
    expect(DB::transactionLevel())->toBeGreaterThan(0); // the surrounding transaction survives

    expect(AuditLog::query()->find($log->id)?->action)->toBe('test.event');
});

it('never stores secrets in the audit context', function (): void {
    $log = app(AuditLogger::class)->record('test.event', null, null, [
        'password' => 'segreta',
        'new_password' => 'segreta',
        'api_token' => 'abc',
        'nested' => ['secret_key' => 'x', 'ok' => 'visibile'],
        'area' => 'admin',
    ]);

    expect($log->fresh()->context)->toBe(['nested' => ['ok' => 'visibile'], 'area' => 'admin']);
});

it('marks a contact request as handled with operator, time and note', function (): void {
    $user = $this->staff(['contact_requests.view', 'contact_requests.manage']);
    $request = ContactRequest::query()->create(['name' => 'A', 'email' => 'a@example.test', 'subject' => 'S', 'message' => 'Messaggio', 'privacy_accepted_at' => now()]);

    $this->actingAsStaff($user)->patch("/gestionale/richieste-contatto/{$request->id}/gestita", ['note' => 'Richiamato'])->assertSessionHasNoErrors();

    $request->refresh();
    expect($request->status->value)->toBe('handled')
        ->and($request->handled_by_staff_id)->toBe($user->id)
        ->and($request->handling_note)->toBe('Richiamato');
});
