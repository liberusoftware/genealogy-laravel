<div>
    <form wire:submit="search" aria-label="External record search">
        <label>First name <input type="text" wire:model="firstName"></label>
        <label>Last name <input type="text" wire:model="lastName"></label>
        <label>Birth year <input type="number" wire:model="birthYear"></label>
        <label>Birth place <input type="text" wire:model="birthPlace"></label>
        <button type="submit">Search external records</button>
        <button type="button" wire:click="clear">Clear</button>
    </form>

    @error('firstName') <p role="alert">{{ $message }}</p> @enderror
    @error('lastName') <p role="alert">{{ $message }}</p> @enderror
    @error('birthYear') <p role="alert">{{ $message }}</p> @enderror
    @error('birthPlace') <p role="alert">{{ $message }}</p> @enderror

    @if ($result)
        @if (! $result['available'])
            <p role="status">External record discovery is unavailable.</p>
        @else
            <p role="status">Provider: {{ $result['provider'] }}</p>
            <ol aria-label="External record candidates">
                @forelse ($result['candidates'] as $match)
                    <li wire:key="genealogy-discovery-external-{{ $loop->index }}">
                        <strong>{{ $match['candidate']['id'] ?? 'Candidate' }}</strong>
                        <span>Score: {{ $match['score'] }}</span>
                    </li>
                @empty
                    <li>No external records found.</li>
                @endforelse
            </ol>
        @endif
    @endif
</div>
