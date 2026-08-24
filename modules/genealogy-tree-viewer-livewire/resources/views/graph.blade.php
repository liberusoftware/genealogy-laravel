<div>
    <label for="genealogy-tree-viewer-person">Root person ID</label>
    <input id="genealogy-tree-viewer-person" type="text" wire:model.live="personId" aria-describedby="genealogy-tree-viewer-help">
    <label for="genealogy-tree-viewer-generations">Generations</label>
    <input id="genealogy-tree-viewer-generations" type="number" min="0" max="12" wire:model.live="generations">
    <p id="genealogy-tree-viewer-help">Enter a person ID to inspect ancestors and descendants.</p>
    @if ($data)
        <div wire:loading.remove>
            <h2>{{ $data['root']['name'] }}</h2>
            <p>{{ count($data['ancestors']) }} ancestors, {{ count($data['descendants']) }} descendants.</p>
        </div>
    @endif
</div>
