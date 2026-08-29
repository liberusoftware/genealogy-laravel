<div>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-collaboration-watch-{{ $record->id }}">
                {{ $record->watchable_type }}:{{ $record->watchable_id }}
                <button type="button" wire:click="unwatch('{{ $record->watchable_type }}', '{{ $record->watchable_id }}')">Unwatch</button>
            </li>
        @endforeach
    </ul>
</div>
