<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Livewire\Components;

use Illuminate\View\View;
use Liberu\Genealogy\GenealogyCore\Actions\CreateTree;
use Liberu\Genealogy\GenealogyCore\Actions\DeleteTree;
use Liberu\Genealogy\GenealogyCore\Actions\SetTreeVisibility;
use Liberu\Genealogy\GenealogyCore\Models\Tree;
use Liberu\Genealogy\GenealogyCore\Policies\TreePolicy;
use Livewire\Component;

final class TreeManager extends Component
{
    public string $name = '';

    public string $description = '';

    public string $identifier = '';

    public bool $isPublic = false;

    public function create(CreateTree $createTree): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'identifier' => ['nullable', 'string', 'alpha_dash', 'max:100'],
            'isPublic' => ['boolean'],
        ]);

        abort_unless(auth()->check(), 403);
        $createTree->execute([
            'name' => $this->name,
            'description' => $this->description,
            'identifier' => $this->identifier !== '' ? $this->identifier : null,
            'is_public' => $this->isPublic,
            'user_id' => auth()->id(),
        ]);
        $this->reset('name', 'description', 'identifier', 'isPublic');
        $this->dispatch('tree-created');
    }

    public function delete(string $treeId, DeleteTree $delete): void
    {
        $tree = Tree::query()->findOrFail($treeId);
        abort_unless((new TreePolicy())->manage(auth()->user(), $tree), 403);
        $delete->execute($tree);
        $this->dispatch('tree-deleted');
    }

    public function toggleVisibility(string $treeId, SetTreeVisibility $visibility): void
    {
        $tree = Tree::query()->findOrFail($treeId);
        abort_unless((new TreePolicy())->manage(auth()->user(), $tree), 403);
        $visibility->execute($tree, ! $tree->is_public);
        $this->dispatch('tree-visibility-updated');
    }

    public function render(): View
    {
        $trees = auth()->check()
            ? Tree::query()->where(fn ($query) => $query->public()->orWhere('user_id', auth()->id()))->latest()->get()
            : Tree::query()->public()->latest()->get();

        return view('genealogy-core-livewire::trees', ['trees' => $trees]);
    }
}
