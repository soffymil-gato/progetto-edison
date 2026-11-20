<?php

declare(strict_types=1);

namespace App\Domain\Settings;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $key
 * @property array<string, string|null> $value
 * @property int|null $updated_by_staff_id
 */
class Setting extends Model
{
    protected $table = 'settings';

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    /** @var list<string> */
    protected $fillable = ['key', 'value', 'updated_by_staff_id'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['value' => 'array', 'updated_by_staff_id' => 'integer'];
    }
}
