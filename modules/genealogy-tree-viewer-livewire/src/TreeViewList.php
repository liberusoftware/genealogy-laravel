<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Livewire;

use Liberu\Genealogy\TreeViewer\Models\TreeView;
use Livewire\Component;

final class TreeViewList extends Component
{
    public string $status = '';

    public string $search = '';

    public function render(): mixed
    {
        return view('genealogy-tree-viewer-livewire::list', [
            'records' => TreeView::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->when($this->search !== '', fn ($query) => $query->where('name', 'like', $this->search.'%'))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
