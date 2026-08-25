<section aria-labelledby="genealogy-trees-heading">
    <h2 id="genealogy-trees-heading">Family trees</h2>

    <form wire:submit="create" aria-label="Create family tree">
        <label>Name <input wire:model="name" type="text" required></label>
        <label>Identifier <input wire:model="identifier" type="text" maxlength="100"></label>
        <label>Description <textarea wire:model="description"></textarea></label>
        <label><input wire:model="isPublic" type="checkbox"> Make this tree public</label>
        @error('name') <p role="alert">{{ $message }}</p> @enderror
        @error('identifier') <p role="alert">{{ $message }}</p> @enderror
        <button type="submit" wire:loading.attr="disabled">Create tree</button>
    </form>

    @if ($trees->isEmpty())
        <p>No trees are available.</p>
    @else
        <ul>
            @foreach ($trees as $tree)
                <li wire:key="tree-{{ $tree->id }}">
                    <span>{{ $tree->name }}</span>
                    @if (auth()->check() && $tree->isOwnedBy(auth()->id()))
                        <button type="button" wire:click="toggleVisibility('{{ $tree->id }}')" wire:confirm="Change this tree's visibility?">
                            {{ $tree->is_public ? 'Make private' : 'Make public' }}
                        </button>
                        <button type="button" wire:click="delete('{{ $tree->id }}')" wire:confirm="Delete this tree?">Delete</button>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</section>
