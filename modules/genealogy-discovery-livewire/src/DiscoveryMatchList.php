<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Livewire;

use Liberu\Genealogy\Discovery\Actions\ReviewDiscoveryMatch;
use Liberu\Genealogy\Discovery\Actions\ScanDuplicateCandidates;
use Liberu\Genealogy\Discovery\Models\DiscoveryMatch;
use Livewire\Component;

final class DiscoveryMatchList extends Component
{
    public string $status = '';

    /** @var array{scanned: int, created: int, existing: int, matches: list<string>}|null */
    public ?array $scanReport = null;

    public function scanDuplicates(ScanDuplicateCandidates $scan): void
    {
        $this->scanReport = $scan->execute();
        $this->dispatch('genealogy-discovery-duplicates-scanned', created: $this->scanReport['created']);
    }

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
