<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Livewire;

use Illuminate\Validation\Rule;
use Liberu\Genealogy\TreeViewer\Models\TreeView;
use Livewire\Component;

final class TreeViewList extends Component
{
    public string $status = '';

    public string $search = '';

    protected function rules(): array
    {
        return ['status' => ['nullable', Rule::in(TreeView::STATUSES)]];
    }

    public function updatedStatus(): void
    {
        $this->validateOnly('status');
    }

    public function render(): mixed
    {
        abort_unless(auth()->check(), 403);

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
