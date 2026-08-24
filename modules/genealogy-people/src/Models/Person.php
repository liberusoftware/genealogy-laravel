<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Person extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_people';

    protected $fillable = [
        'given_name', 'family_name', 'display_name', 'birth_date', 'death_date',
        'birth_place', 'death_place', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'death_date' => 'date',
            'metadata' => 'array',
        ];
    }

    public function getDisplayNameAttribute(?string $value): string
    {
        return $value ?: trim($this->given_name.' '.($this->family_name ?? ''));
    }

    public function isLiving(): bool
    {
        return $this->death_date === null;
    }
}
