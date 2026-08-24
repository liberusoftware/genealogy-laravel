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
                ->when($this->query !== '', function ($builder): void {
                    // Keep the OR predicates inside the same grouped clause
                    // as the team global scope. An ungrouped orWhere would
                    // allow matching rows from another team to escape the
                    // tenant boundary.
                    $builder->where(function ($search): void {
                        $search->where('given_name', 'like', $this->query.'%')
                            ->orWhere('family_name', 'like', $this->query.'%')
                            ->orWhere('display_name', 'like', $this->query.'%');
                    });
                })
                ->orderBy('family_name')
                ->limit(25)
                ->get(),
        ]);
    }
}
