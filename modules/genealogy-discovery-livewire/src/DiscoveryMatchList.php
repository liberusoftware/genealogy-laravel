<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Livewire;

use Liberu\Genealogy\Discovery\Actions\ReviewDiscoveryMatch;
use Liberu\Genealogy\Discovery\Models\DiscoveryMatch;
use Livewire\Component;

final class DiscoveryMatchList extends Component
{
    public string $status = '';

    public function review(string $id, string $status, ReviewDiscoveryMatch $review): void
    {
        abort_unless(auth()->check(), 403);
        $match = DiscoveryMatch::query()->findOrFail($id);
        $review->execute($match, $status);
    }

    public function render(): mixed
    {
        return view('genealogy-discovery-livewire::list', [
            'records' => DiscoveryMatch::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
