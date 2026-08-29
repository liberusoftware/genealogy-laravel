<div>
    <h2>DNA consent history</h2>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-dna-consent-{{ $record->id }}">
                {{ $record->scope }} — {{ $record->granted ? 'Granted' : 'Revoked' }}
            </li>
        @endforeach
    </ul>
</div>
