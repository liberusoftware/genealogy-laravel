<section aria-label="Person details">
    <h2>{{ $person->display_name }}</h2>
    <p>{{ $person->isLiving() ? 'Living' : 'Deceased' }}</p>
    <h3>Names</h3>
    <ul>@foreach ($person->names as $name)<li wire:key="name-{{ $name->id }}">{{ trim($name->given_name.' '.$name->family_name) }}</li>@endforeach</ul>
    <h3>Identities</h3>
    <ul>@foreach ($person->identities as $identity)<li wire:key="identity-{{ $identity->id }}">{{ $identity->type }}: {{ $identity->value }}</li>@endforeach</ul>
    <h3>Life events</h3>
    <ul>@foreach ($person->lifeEvents as $event)<li wire:key="life-event-{{ $event->id }}">{{ $event->type }} {{ $event->date?->toDateString() }}</li>@endforeach</ul>
</section>
