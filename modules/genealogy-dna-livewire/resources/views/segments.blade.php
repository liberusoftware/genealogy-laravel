<div>
    <h2>DNA match segments</h2>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-dna-segment-{{ $record->id }}">
                Chromosome {{ $record->chromosome }}: {{ $record->start_position }}–{{ $record->end_position }}
                @if ($record->centimorgans !== null)
                    ({{ $record->centimorgans }} cM)
                @endif
            </li>
        @endforeach
    </ul>
</div>
