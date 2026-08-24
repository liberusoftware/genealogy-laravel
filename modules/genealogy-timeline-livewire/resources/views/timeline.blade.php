<div>
    <form wire:submit="$refresh" aria-label="Timeline filters">
        <label>Person ID <input type="text" wire:model="subjectPersonId"></label>
        <label>Family key <input type="text" wire:model="familyKey"></label>
        <label>From <input type="date" wire:model="from"></label>
        <label>To <input type="date" wire:model="to"></label>
        <label><input type="checkbox" wire:model="includePrivate"> Include private events</label>
        <button type="submit">Apply</button>
    </form>
    @error('to') <p role="alert">{{ $message }}</p> @enderror
    <ol aria-label="Chronological events">
        @forelse ($events as $event)
            <li wire:key="genealogy-timeline-{{ $event['id'] }}">
                <time>{{ $event['event_date'] ?? $event['date_start'] ?? 'Undated' }}</time>
                <strong>{{ $event['name'] }}</strong>
                <span>{{ $event['kind'] }}</span>
                @if ($event['conflict_group']) <small>Conflict: {{ $event['conflict_group'] }}</small> @endif
            </li>
        @empty
            <li>No timeline events found.</li>
        @endforelse
    </ol>
</div>
