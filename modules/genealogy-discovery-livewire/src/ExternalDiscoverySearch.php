<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Livewire;

use Liberu\Genealogy\Discovery\Services\ExternalRecordMatcher;
use Livewire\Component;

final class ExternalDiscoverySearch extends Component
{
    public string $firstName = '';

    public string $lastName = '';

    public ?int $birthYear = null;

    public string $birthPlace = '';

    /** @var array{available: bool, provider: ?string, candidates: list<array<string, mixed>>, error: ?string}|null */
    public ?array $result = null;

    public function search(ExternalRecordMatcher $matcher): void
    {
        $values = $this->validate([
            'firstName' => ['nullable', 'string', 'max:255'],
            'lastName' => ['nullable', 'string', 'max:255'],
            'birthYear' => ['nullable', 'integer', 'between:1,3000'],
            'birthPlace' => ['nullable', 'string', 'max:255'],
        ]);

        $this->result = $matcher->execute([
            'first_name' => $values['firstName'],
            'last_name' => $values['lastName'],
            'birth_year' => $values['birthYear'],
            'birth_place' => $values['birthPlace'],
        ]);
    }

    public function clear(): void
    {
        $this->reset(['firstName', 'lastName', 'birthYear', 'birthPlace', 'result']);
        $this->resetValidation();
    }

    public function render(): mixed
    {
        return view('genealogy-discovery-livewire::external-search');
    }
}
