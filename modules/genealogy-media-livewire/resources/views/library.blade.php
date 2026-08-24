<div>
    <form wire:submit="$refresh" aria-label="Media library filters">
        <label>Search <input type="search" wire:model="term"></label>
        <label>Type
            <select wire:model="kind">
                <option value="">All</option>
                @foreach (\Liberu\Genealogy\Media\Models\MediaAsset::KINDS as $option)
                    <option value="{{ $option }}">{{ ucfirst($option) }}</option>
                @endforeach
            </select>
        </label>
        <label><input type="checkbox" wire:model="publicOnly"> Public only</label>
        <button type="submit">Filter</button>
    </form>
    @error('kind') <p role="alert">{{ $message }}</p> @enderror
    <ul aria-label="Media assets">
        @forelse ($assets as $asset)
            <li wire:key="genealogy-media-{{ $asset['id'] }}">
                <strong>{{ $asset['name'] }}</strong>
                <span>{{ $asset['kind'] }}</span>
                @if ($asset['rights_status']) <small>{{ $asset['rights_status'] }}</small> @endif
                @if ($asset['transcription_status'] === 'completed') <small>Transcribed</small> @endif
            </li>
        @empty
            <li>No media assets found.</li>
        @endforelse
    </ul>
</div>
