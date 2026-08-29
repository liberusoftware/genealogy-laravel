<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class Person extends Model
{
    public const GENDER_MALE = 'M';

    public const GENDER_FEMALE = 'F';

    public const GENDER_UNKNOWN = 'U';

    public const GENDER_OTHER = 'X';

    public const SEX_OPTIONS = [
        self::GENDER_MALE,
        self::GENDER_FEMALE,
        self::GENDER_UNKNOWN,
        self::GENDER_OTHER,
    ];

    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_people';

    protected $fillable = [
        'team_id', 'given_name', 'family_name', 'display_name', 'sex', 'aliases', 'attributes',
        'birth_date', 'death_date', 'birth_place', 'death_place', 'is_public', 'metadata',
    ];

    public function names(): HasMany
    {
        return $this->hasMany(PersonName::class);
    }

    public function lifeEvents(): HasMany
    {
        return $this->hasMany(PersonLifeEvent::class);
    }

    public function identities(): HasMany
    {
        return $this->hasMany(PersonIdentity::class);
    }

    public function mergeCandidates(): HasMany
    {
        return $this->hasMany(MergeCandidate::class);
    }

    public function associations(): HasMany
    {
        return $this->hasMany(PersonAssociation::class);
    }

    public function associatedWith(): HasMany
    {
        return $this->hasMany(PersonAssociation::class, 'associated_person_id');
    }

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'death_date' => 'date',
            'aliases' => 'array',
            'attributes' => 'array',
            'is_public' => 'boolean',
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

    public function isDeceased(): bool
    {
        return ! $this->isLiving();
    }

    public function getSex(): string
    {
        return $this->sex ?: self::GENDER_UNKNOWN;
    }

    public static function normalizeSex(mixed $sex): ?string
    {
        if ($sex === null || trim((string) $sex) === '') {
            return null;
        }

        $normalized = strtoupper(trim((string) $sex));

        if (! in_array($normalized, self::SEX_OPTIONS, true)) {
            throw new \InvalidArgumentException('A person sex must be one of M, F, U, or X.');
        }

        return $normalized;
    }

    public function scopeLiving(Builder $query): Builder
    {
        return $query->whereNull($query->getModel()->qualifyColumn('death_date'));
    }

    public function scopeDeceased(Builder $query): Builder
    {
        return $query->whereNotNull($query->getModel()->qualifyColumn('death_date'));
    }

    public function fullName(): string
    {
        return $this->display_name;
    }
}
