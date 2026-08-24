<div>
    <label for="genealogy-person-search">Search people</label>
    <input id="genealogy-person-search" wire:model.live.debounce.300ms="query" type="search">
    <ul>
        @foreach ($people as $person)
            <li wire:key="person-{{ $person->id }}">{{ $person->display_name }}</li>
        @endforeach
    </ul>
</div>
