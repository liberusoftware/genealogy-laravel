<div>
    <label for="genealogy-relationships-list-type">Relationship type</label>
    <select id="genealogy-relationships-list-type" wire:model.live="type">
        <option value="">All</option>
        @foreach (\Liberu\Genealogy\Relationships\Models\Relationship::TYPES as $relationshipType)
            <option value="{{ $relationshipType }}">{{ ucfirst($relationshipType) }}</option>
        @endforeach
    </select>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-relationships-list-{{ $record->id }}">{{ $record->type }}: {{ $record->person_id }} → {{ $record->related_person_id }}</li>
        @endforeach
    </ul>
</div>
