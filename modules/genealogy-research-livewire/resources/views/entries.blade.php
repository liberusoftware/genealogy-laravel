<div>
    <label for="genealogy-research-entry-project">Project ID</label>
    <input id="genealogy-research-entry-project" type="text" wire:model.live="projectId">
    <label for="genealogy-research-entry-status">Status</label>
    <select id="genealogy-research-entry-status" wire:model.live="status">
        <option value="">All</option>
        <option value="open">Open</option>
        <option value="in_progress">In progress</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
    </select>
    <ul>
        @forelse ($records as $record)
            <li wire:key="genealogy-research-entry-{{ $record->id }}">
                <strong>{{ $record->title }}</strong>
                <span>{{ $record->status }}</span>
                @if ($record->status !== 'completed')
                    <button type="button" wire:click="complete('{{ $record->id }}')" wire:loading.attr="disabled">Complete</button>
                @endif
            </li>
        @empty
            <li>No research entries found.</li>
        @endforelse
    </ul>
</div>
