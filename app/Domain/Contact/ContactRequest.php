<?php

declare(strict_types=1);

namespace App\Domain\Contact;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $company
 * @property string $email
 * @property string|null $phone
 * @property string $subject
 * @property string $message
 * @property Carbon $privacy_accepted_at
 * @property ContactRequestStatus $status
 * @property int|null $handled_by_staff_id
 * @property Carbon|null $handled_at
 * @property string|null $handling_note
 * @property Carbon $created_at
 */
class ContactRequest extends Model
{
    protected $table = 'contact_requests';

    /** @var list<string> */
    protected $fillable = ['name', 'company', 'email', 'phone', 'subject', 'message', 'privacy_accepted_at'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'privacy_accepted_at' => 'datetime',
            'handled_at' => 'datetime',
            'handled_by_staff_id' => 'integer',
            'status' => ContactRequestStatus::class,
        ];
    }
}
