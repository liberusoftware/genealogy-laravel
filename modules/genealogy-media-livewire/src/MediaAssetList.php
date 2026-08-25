<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Livewire;

use Liberu\Genealogy\Media\Models\MediaAsset;
use Livewire\Component;

final class MediaAssetList extends Component
{
    public string $status = '';

    public function render(): mixed
    {
        return view('genealogy-media-livewire::list', [
            'records' => MediaAsset::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
