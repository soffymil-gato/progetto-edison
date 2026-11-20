<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

use App\Domain\Audit\AuditLogger;
use App\Domain\Identity\Enums\AccountStatus;
use App\Domain\Identity\Models\Account;
use App\Domain\Identity\Models\AccountInvitation;
use App\Domain\Identity\Models\StaffUser;
use App\Domain\Identity\Notifications\AccountInvitationNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Accounts are never self-registered: an administrator creates the account
 * and an invitation link lets the person choose their own password.
 * The token travels only in the email; the database keeps its SHA-256 hash.
 */
final class InvitationService
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** @return string The plain token (sent by email; returned only for CLI bootstrap). */
    public function invite(Account $account, ?StaffUser $by): string
    {
        $token = Str::random(64);

        DB::transaction(function () use ($account, $by, $token): void {
            $this->revokePending($account);

            AccountInvitation::query()->create([
                'account_type' => $account->accountType(),
                'account_id' => $account->getKey(),
                'token_hash' => self::hash($token),
                'expires_at' => now()->addHours((int) config('edison.auth.invitation_ttl_hours')),
                'created_by_staff_id' => $by?->id,
            ]);

            $this->audit->record('account.invitation.sent', $by, $account, ['account_type' => $account->accountType()->value]);
        });

        $account->notify(new AccountInvitationNotification($token, $account->accountType()));

        return $token;
    }

    /** Returns the pending invitation matching the token, if still valid. */
    public function findValid(#[\SensitiveParameter] string $token): ?AccountInvitation
    {
        if (strlen($token) !== 64) {
            return null;
        }

        $invitation = AccountInvitation::query()->pending()->where('token_hash', self::hash($token))->first();
        $account = $invitation?->account();

        if ($account === null || $account->status !== AccountStatus::Invited) {
            return null;
        }

        return $invitation;
    }

    public function accept(AccountInvitation $invitation, #[\SensitiveParameter] string $password): Account
    {
        return DB::transaction(function () use ($invitation, $password): Account {
            $account = $invitation->account();
            if ($account === null) {
                throw new \RuntimeException('Invitation without account.');
            }

            $account->password = $password;
            $account->status = AccountStatus::Active;
            $account->password_changed_at = now();
            $account->save();

            $invitation->accepted_at = now();
            $invitation->save();

            $this->audit->record('account.invitation.accepted', $account, $account);

            return $account;
        });
    }

    public function revokePending(Account $account): void
    {
        AccountInvitation::query()
            ->pending()
            ->where('account_type', $account->accountType()->value)
            ->where('account_id', $account->getKey())
            ->update(['revoked_at' => now()]);
    }

    private static function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
