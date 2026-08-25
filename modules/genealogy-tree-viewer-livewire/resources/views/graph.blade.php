<div>
    <label for="genealogy-tree-viewer-person">Root person ID</label>
    <input id="genealogy-tree-viewer-person" type="text" wire:model.live="personId" aria-describedby="genealogy-tree-viewer-help">
    <label for="genealogy-tree-viewer-generations">Generations</label>
    <input id="genealogy-tree-viewer-generations" type="number" min="0" max="12" wire:model.live="generations" wire:change="setGenerations($event.target.value)">
    <label for="genealogy-tree-viewer-view">View</label>
    <select id="genealogy-tree-viewer-view" wire:model.live="view" wire:change="setView($event.target.value)">
        <option value="chart">Chart</option>
        <option value="pedigree">Pedigree</option>
        <option value="descendants">Descendants</option>
        <option value="fan">Fan</option>
    </select>
    <label><input type="checkbox" wire:model.live="includeLiving"> Include living people</label>
    <button type="button" wire:click="loadGraph" wire:loading.attr="disabled">Load tree</button>
    <p id="genealogy-tree-viewer-help">Enter a person ID to inspect ancestors and descendants.</p>
    @if ($data !== [])
        <div wire:loading.remove>
            <h2>{{ $data['root']['name'] }}</h2>
            <p>{{ count($data['ancestors']) }} ancestors, {{ count($data['descendants']) }} descendants.</p>
            <p>{{ count($data['nodes']) }} nodes, {{ count($data['edges']) }} relationships.</p>
        </div>
    @endif
</div>
