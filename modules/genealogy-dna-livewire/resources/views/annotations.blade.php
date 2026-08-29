<div>
    <section aria-labelledby="genealogy-dna-notes-heading">
        <h2 id="genealogy-dna-notes-heading">DNA notes</h2>
        <ul>
            @foreach ($notes as $note)
                <li wire:key="genealogy-dna-note-{{ $note->id }}">{{ $note->body }}</li>
            @endforeach
        </ul>
    </section>
    <section aria-labelledby="genealogy-dna-relationships-heading">
        <h2 id="genealogy-dna-relationships-heading">DNA relationships</h2>
        <ul>
            @foreach ($relationships as $relationship)
                <li wire:key="genealogy-dna-relationship-{{ $relationship->id }}">{{ $relationship->relationship_type }} ({{ $relationship->status }})</li>
            @endforeach
        </ul>
    </section>
</div>
