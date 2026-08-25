<div>
    <label for="genealogy-evidence-list-status">Status</label>
    <select id="genealogy-evidence-list-status" wire:model.live="status">
        <option value="">All</option>
        @foreach (\Liberu\Genealogy\Evidence\Models\EvidenceRecord::STATUSES as $statusOption)
            <option value="{{ $statusOption }}">{{ ucfirst($statusOption) }}</option>
        @endforeach
    </select>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-evidence-list-{{ $record->id }}">
                <span>{{ $record->name }}</span>
                @if ($record->status !== 'completed' && $record->status !== 'archived')
                    <button type="button" wire:click="review('{{ $record->id }}')" wire:loading.attr="disabled">
                        Review
                    </button>
                @endif
                @if ($record->status !== 'archived')
                    <button type="button" wire:click="archive('{{ $record->id }}')" wire:loading.attr="disabled">
                        Archive
                    </button>
                @endif
            </li>
        @endforeach
    </ul>
</div>
