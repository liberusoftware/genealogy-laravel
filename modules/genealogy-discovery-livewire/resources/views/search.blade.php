<div>
    <form wire:submit="search" aria-label="Genealogy discovery search">
        <label for="genealogy-discovery-search-term">Search people, places, or sources</label>
        <input id="genealogy-discovery-search-term" type="search" wire:model="term" autocomplete="off">
        <label><input type="checkbox" wire:model="publicOnly"> Public records only</label>
        <button type="submit">Search</button>
        <button type="button" wire:click="clear">Clear</button>
    </form>
    @error('term') <p role="alert">{{ $message }}</p> @enderror

    @foreach (['people' => 'People', 'places' => 'Places', 'sources' => 'Sources'] as $type => $label)
        <section aria-labelledby="genealogy-discovery-{{ $type }}">
            <h2 id="genealogy-discovery-{{ $type }}">{{ $label }}</h2>
            @forelse ($results[$type] as $result)
                <div wire:key="genealogy-discovery-{{ $type }}-{{ $result['id'] }}">
                    <span>{{ $result['name'] }}</span>
                    @if ($type === 'people' && $result['is_living']) <small>Living</small> @endif
                    @if ($type === 'places' && $result['jurisdiction']) <small>{{ $result['jurisdiction'] }}</small> @endif
                    @if ($type === 'sources' && $result['citation']) <small>{{ $result['citation'] }}</small> @endif
                </div>
            @empty
                <p>No results.</p>
            @endforelse
        </section>
    @endforeach
</div>
