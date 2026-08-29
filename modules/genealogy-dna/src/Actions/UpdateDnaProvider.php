<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Liberu\Genealogy\Dna\Models\DnaProvider;
use Liberu\Genealogy\GenealogyCore\TeamContext;

final class UpdateDnaProvider
{
    public function execute(DnaProvider $provider, array $attributes): DnaProvider
    {
        if ((string) $provider->team_id !== app(TeamContext::class)->require()) {
            throw new InvalidArgumentException('The DNA provider must belong to the active team.');
        }
        $values = Arr::only($attributes, ['name', 'slug', 'status', 'website', 'metadata']);
        if (array_key_exists('name', $values)) {
            $values['name'] = trim((string) $values['name']);
            if ($values['name'] === '') {
                throw ValidationException::withMessages(['name' => 'A DNA provider name is required.']);
            }
        }
        if (array_key_exists('slug', $values) || array_key_exists('name', $values)) {
            $values['slug'] = Str::slug((string) ($values['slug'] ?? $values['name']));
            if ($values['slug'] === '') {
                throw ValidationException::withMessages(['slug' => 'A DNA provider slug is required.']);
            }
        }
        DB::transaction(fn (): bool => $provider->update($values));

        return $provider->refresh();
    }
}
