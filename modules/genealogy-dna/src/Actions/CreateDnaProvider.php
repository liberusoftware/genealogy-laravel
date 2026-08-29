<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\Dna\Models\DnaProvider;

final class CreateDnaProvider
{
    public function execute(array $attributes): DnaProvider
    {
        $values = Arr::only($attributes, ['name', 'slug', 'status', 'website', 'metadata']);
        $values['name'] = trim((string) ($values['name'] ?? ''));
        if ($values['name'] === '') {
            throw ValidationException::withMessages(['name' => 'A DNA provider name is required.']);
        }
        $values['slug'] = Str::slug((string) ($values['slug'] ?? $values['name']));
        if ($values['slug'] === '') {
            throw ValidationException::withMessages(['slug' => 'A DNA provider slug is required.']);
        }
        $values['status'] ??= 'active';

        return DnaProvider::query()->create($values);
    }
}
