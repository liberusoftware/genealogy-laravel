<div>
    <label>
        <span class="sr-only">Search {{ $entity }}</span>
        <input type="search" wire:model.live="search" placeholder="Search {{ $entity }}" aria-label="Search {{ $entity }}">
    </label>

    <ul aria-label="{{ ucfirst($entity) }}">
        @forelse ($records as $record)
            <li wire:key="evidence-{{ $record['id'] }}">
                {{ $record['name'] ?? $record['title'] ?? $record['statement'] ?? $record['conclusion'] ?? $record['id'] }}
                <button type="button" wire:click="delete('{{ $record['id'] }}')" wire:confirm="Delete this record?">Delete</button>
            </li>
        @empty
            <li>No {{ $entity }} found.</li>
        @endforelse
    </ul>
</div>
