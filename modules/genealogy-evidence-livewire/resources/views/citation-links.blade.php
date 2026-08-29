<section aria-label="Citation person links">
    <h2>People linked to {{ $citation->title ?: 'citation' }}</h2>
    <ul>
        @foreach ($citation->personLinks as $link)
            <li wire:key="citation-link-{{ $link->id }}">{{ $link->subject?->display_name }} — {{ $link->group }} — {{ $link->qualityLabel() }}</li>
        @endforeach
    </ul>
    <input wire:model="subjectPersonId" placeholder="Person UUID">
    <select wire:model="group"><option value="indi">Person</option><option value="indi_name">Name</option><option value="indi_even">Event</option><option value="indi_asso">Association</option><option value="indi_lds">LDS</option></select>
    <input wire:model="page" placeholder="Page">
    <input wire:model="quality" placeholder="Quality 0–3">
    <textarea wire:model="text" placeholder="Link text"></textarea>
    <button type="button" wire:click="add">Link citation</button>
</section>
