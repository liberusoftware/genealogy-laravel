<?php

declare(strict_types=1);

namespace Liberu\Genealogy\People\Livewire;

use Liberu\Genealogy\People\Models\Person;
use Livewire\Component;

final class PersonSearch extends Component
{
    public string $query = '';

    public function render(): mixed
    {
        return view('genealogy-people-livewire::person-search', [
            'people' => Person::query()
                ->when($this->query !== '', fn ($builder) => $builder->where('given_name', 'like', $this->query.'%')->orWhere('family_name', 'like', $this->query.'%'))
                ->orderBy('family_name')
                ->limit(25)
                ->get(),
        ]);
    }
}
