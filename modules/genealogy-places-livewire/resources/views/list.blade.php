<div>
    <label for="genealogy-places-list-status">Status</label>
    <select id="genealogy-places-list-status" wire:model.live="status">
        <option value="">All</option>
        @foreach (\Liberu\Genealogy\Places\Models\Place::STATUSES as $statusOption)
            <option value="{{ $statusOption }}">{{ ucfirst($statusOption) }}</option>
        @endforeach
    </select>
    <ul>
        @foreach ($hierarchy as $record)
            <li wire:key="genealogy-places-list-{{ $record['id'] }}" style="padding-left: {{ $record['depth'] * 1.5 }}rem">{{ $record['name'] }}</li>
        @endforeach
    </ul>
</div>
