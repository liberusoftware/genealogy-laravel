<div>
    <label for="genealogy-person-search">Search people</label>
    <input id="genealogy-person-search" wire:model.live.debounce.300ms="query" type="search">
    <label><input type="checkbox" wire:model.live="includeDeceased"> Include deceased people</label>
    <ul>
        @foreach ($people as $person)
            <li wire:key="person-{{ $person->id }}">{{ $person->display_name }}</li>
        @endforeach
    </ul>
</div>
