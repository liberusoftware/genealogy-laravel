<section aria-label="Person details">
    <h2>{{ $person->display_name }}</h2>
    <p>{{ $person->isLiving() ? 'Living' : 'Deceased' }}</p>
    <h3>Names</h3>
    <ul>@foreach ($person->names as $name)<li wire:key="name-{{ $name->id }}">{{ trim($name->given_name.' '.$name->family_name) }}</li>@endforeach</ul>
    <h3>Identities</h3>
    <ul>@foreach ($person->identities as $identity)<li wire:key="identity-{{ $identity->id }}">{{ $identity->type }}: {{ $identity->value }}</li>@endforeach</ul>
    <h3>Life events</h3>
    <ul>@foreach ($person->lifeEvents as $event)<li wire:key="life-event-{{ $event->id }}">{{ $event->type }} {{ $event->date?->toDateString() }}</li>@endforeach</ul>
    <h3>Attributes</h3>
    <pre>{{ json_encode($person->attributes ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
    <ul>
        @foreach ($person->attributes ?? [] as $attribute => $value)
            <li wire:key="attribute-{{ $attribute }}">
                <span>{{ $attribute }}: {{ is_scalar($value) ? $value : json_encode($value) }}</span>
                <button type="button" wire:click="removeAttribute(@js($attribute))">Remove</button>
            </li>
        @endforeach
    </ul>
    <label for="person-attributes">Edit attributes</label>
    <textarea id="person-attributes" wire:model="attributesJson"></textarea>
    <button type="button" wire:click="updateAttributes">Save attributes</button>
</section>
