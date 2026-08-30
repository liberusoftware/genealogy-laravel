<div>
    <label for="genealogy-import-export-list-status">Status</label>
    <select id="genealogy-import-export-list-status" wire:model.live="status">
        <option value="">All</option>
        @foreach (\Liberu\Genealogy\ImportExport\Models\DataTransfer::STATUSES as $transferStatus)
            <option value="{{ $transferStatus }}">{{ ucfirst(str_replace('_', ' ', $transferStatus)) }}</option>
        @endforeach
    </select>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-import-export-list-{{ $record->id }}">{{ $record->name }}</li>
        @endforeach
    </ul>
</div>
