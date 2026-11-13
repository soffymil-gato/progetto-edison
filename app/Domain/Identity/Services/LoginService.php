<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

use App\Domain\Audit\AuditLogger;
use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Enums\AccountType;
use App\Domain\Identity\Models\Account;
use App\Domain\Identity\Models\PortalAccount;
use App\Domain\Identity\Models\StaffUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Login for both areas.
 *
 * - Throttling per identifier (whether or not the account exists) and per IP.
 * - Identical message and comparable timing for unknown accounts and wrong
 *   passwords, so that accounts cannot be enumerated.
 * - The raw identifier of unknown accounts is never logged (users sometimes
 *   type their password in the username field): only a fingerprint.
 */
final class LoginService
{
    public const INVALID_CREDENTIALS = 'Credenziali non valide. Controlla i dati inseriti e riprova.';

    public const ACCOUNT_DISABLED = 'Accesso non consentito: l\'account non è attivo. Contatta l\'amministrazione.';

    public function __construct(private readonly AuditLogger $audit) {}

    public function attempt(AccountType $type, string $identifier, #[\SensitiveParameter] string $password, Request $request): LoginResult
    {
        $identifier = Str::lower(trim($identifier));
        $fingerprint = substr(hash('sha256', $type->value.'|'.$identifier), 0, 16);
        $identifierKey = 'login:'.$type->value.':id:'.$fingerprint;
        $ipKey = 'login:'.$type->value.':ip:'.$request->ip();

        $maxPerIdentifier = (int) config('edison.auth.max_attempts_per_identifier');
        $maxPerIp = (int) config('edison.auth.max_attempts_per_ip');

        if (RateLimiter::tooManyAttempts($identifierKey, $maxPerIdentifier) || RateLimiter::tooManyAttempts($ipKey, $maxPerIp)) {
            $seconds = max(RateLimiter::availableIn($identifierKey), RateLimiter::availableIn($ipKey));
            $this->audit->record('auth.login.throttled', null, null, ['area' => $type->area()->value, 'identifier_fingerprint' => $fingerprint]);

            return LoginResult::failure('Troppi tentativi di accesso. Riprova tra '.max(1, (int) ceil($seconds / 60)).' minuti.');
        }

        $account = $this->findAccount($type, $identifier);
        // Always run exactly one hash verification, even for unknown accounts or
        // accounts without a password (invited), to keep timing comparable.
        $storedHash = $account?->password;
        $verified = Hash::check($password, $storedHash ?? $this->dummyHash());
        $passwordMatches = $storedHash !== null && $verified;

        if ($account === null || ! $passwordMatches) {
            RateLimiter::hit($identifierKey, (int) config('edison.auth.identifier_decay_seconds'));
            RateLimiter::hit($ipKey, (int) config('edison.auth.ip_decay_seconds'));
            $this->audit->record('auth.login.failed', null, $account, ['area' => $type->area()->value, 'identifier_fingerprint' => $fingerprint]);

            return LoginResult::failure(self::INVALID_CREDENTIALS);
        }

        if ($account->status !== AccountStatus::Active) {
            $this->audit->record('auth.login.blocked_inactive', $account, $account, ['area' => $type->area()->value, 'status' => $account->status->value]);

            return LoginResult::failure(self::ACCOUNT_DISABLED);
        }

        RateLimiter::clear($identifierKey);

        Auth::guard($type->guard())->login($account);
        $request->session()->regenerate();
        $request->session()->put('auth.started_at', now()->getTimestamp());

        if (Hash::needsRehash((string) $account->password)) {
            $account->password = $password;
        }
        $account->last_login_at = now();
        $account->save();

        $this->audit->record('auth.login.succeeded', $account, $account, ['area' => $type->area()->value]);

        return LoginResult::success($account);
    }

    public function logout(AccountType $type, Request $request): void
    {
        $guard = Auth::guard($type->guard());
        $account = $guard->user();

        if ($account instanceof Account) {
            $this->audit->record('auth.logout', $account, $account, ['area' => $type->area()->value]);
        }

        $guard->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    private function findAccount(AccountType $type, string $identifier): ?Account
    {
        if ($identifier === '') {
            return null;
        }

        return match ($type) {
            AccountType::Staff => StaffUser::query()->where('email', $identifier)->first(),
            AccountType::Customer => PortalAccount::query()
                ->where('email', $identifier)
                ->orWhere('username', $identifier)
                ->first(),
        };
    }

    /** Hash with the current parameters, used to equalise timing for unknown accounts. */
    private function dummyHash(): string
    {
        $key = 'auth.dummy_hash.'.md5((string) json_encode(config('hashing.argon')));

        /** @var string $hash */
        $hash = Cache::rememberForever($key, static fn (): string => Hash::make(Str::random(40)));

        return $hash;
    }
}
