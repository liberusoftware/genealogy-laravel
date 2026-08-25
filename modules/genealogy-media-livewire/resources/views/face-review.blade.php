<div>
    <h2>Face review</h2>

    @error('review') <p role="alert">{{ $message }}</p> @enderror

    @if ($currentTag)
        <p>Media asset: <code>{{ $currentTag['media_asset_id'] }}</code></p>
        @if ($currentTag['confidence'] !== null)
            <p>Provider confidence: {{ $currentTag['confidence'] }}</p>
        @endif
        <label for="genealogy-media-face-person">Person ID</label>
        <input id="genealogy-media-face-person" type="text" wire:model="selectedPersonId" autocomplete="off">
        @error('selectedPersonId') <p role="alert">{{ $message }}</p> @enderror

        <div role="group" aria-label="Face tag actions">
            <button type="button" wire:click="confirm">Confirm</button>
            <button type="button" wire:click="reject">Reject</button>
            <button type="button" wire:click="skip">Skip</button>
            <button type="button" wire:click="previous">Previous</button>
        </div>
        <p>Reviewing {{ $currentTagIndex + 1 }} of {{ $totalTags }}</p>
    @else
        <p>No face tags are waiting for review.</p>
    @endif
</div>
