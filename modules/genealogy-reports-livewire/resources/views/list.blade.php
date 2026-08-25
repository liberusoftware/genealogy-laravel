<div>
    <label for="genealogy-reports-list-status">Status</label>
    <select id="genealogy-reports-list-status" wire:model.live="status">
        <option value="">All</option>
        <option value="draft">Draft</option>
        <option value="active">Active</option>
        <option value="completed">Completed</option>
    </select>
    <label for="genealogy-reports-list-format">Format</label>
    <select id="genealogy-reports-list-format" wire:model.live="format">
        <option value="json">JSON</option>
        <option value="csv">CSV</option>
        <option value="gedcom">GEDCOM</option>
        <option value="svg">SVG</option>
    </select>
    <label for="genealogy-reports-list-root-person">Root person (optional)</label>
    <input id="genealogy-reports-list-root-person" type="text" wire:model.live="rootPersonId">
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-reports-list-{{ $record->id }}">
                {{ $record->name }}
                <button type="button" wire:click="generate('{{ $record->id }}')">Generate</button>
                @if ($generatedReportId === $record->id && $record->generated_output)
                    <pre>{{ is_string($record->generated_output['content']) ? $record->generated_output['content'] : json_encode($record->generated_output['content'], JSON_PRETTY_PRINT) }}</pre>
                @endif
            </li>
        @endforeach
    </ul>
</div>
