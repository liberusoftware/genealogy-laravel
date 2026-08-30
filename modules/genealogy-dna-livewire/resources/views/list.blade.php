<div>
    <form wire:submit="import" aria-label="Import DNA kit">
        <label for="genealogy-dna-kit-name">Kit name</label>
        <input id="genealogy-dna-kit-name" type="text" wire:model="name" required>
        <label for="genealogy-dna-kit-content">Raw DNA content</label>
        <textarea id="genealogy-dna-kit-content" wire:model="content" required></textarea>
        <label for="genealogy-dna-kit-consent">Consent status</label>
        <select id="genealogy-dna-kit-consent" wire:model="consentStatus">
            <option value="pending">Pending</option><option value="granted">Granted</option><option value="revoked">Revoked</option>
        </select>
        @error('name') <p role="alert">{{ $message }}</p> @enderror
        @error('content') <p role="alert">{{ $message }}</p> @enderror
        @error('consentStatus') <p role="alert">{{ $message }}</p> @enderror
        <button type="submit" wire:loading.attr="disabled">Import kit</button>
    </form>
    <label for="genealogy-dna-list-status">Status</label>
    <select id="genealogy-dna-list-status" wire:model.live="status">
        <option value="">All</option>
        @foreach (\Liberu\Genealogy\Dna\Models\DnaKit::STATUSES as $statusOption)
            <option value="{{ $statusOption }}">{{ ucfirst($statusOption) }}</option>
        @endforeach
    </select>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-dna-list-{{ $record->id }}">{{ $record->name }} ({{ $record->consent_status }})</li>
        @endforeach
    </ul>
</div>
