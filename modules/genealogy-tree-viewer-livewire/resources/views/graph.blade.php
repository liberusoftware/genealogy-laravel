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
    <label><input type="checkbox" wire:model.live="includeSiblings"> Include siblings</label>
    <label for="genealogy-tree-viewer-max-nodes">Maximum nodes</label>
    <input id="genealogy-tree-viewer-max-nodes" type="number" min="100" max="5000" wire:model.live="maxNodes">
    <button type="button" wire:click="loadGraph" wire:loading.attr="disabled">Load tree</button>
    <p id="genealogy-tree-viewer-help">Enter a person ID to inspect ancestors and descendants.</p>
    @if ($data !== [])
        <div wire:loading.remove>
            <h2>{{ $data['root']['name'] }}</h2>
            <p>{{ count($data['ancestors']) }} ancestors, {{ count($data['descendants']) }} descendants.</p>
            <p>{{ count($data['nodes']) }} nodes, {{ count($data['edges']) }} relationships.</p>
            @if (($data['navigation']['truncated'] ?? false) === true)
                <p role="status">This graph reached its node limit. Narrow the view or increase the maximum.</p>
            @endif
            <ul aria-label="Tree nodes">
                @foreach ($data['nodes'] as $node)
                    <li wire:key="genealogy-tree-node-{{ $node['id'] }}">
                        <button type="button" wire:click="navigateTo('{{ $node['id'] }}')" wire:loading.attr="disabled">
                            {{ $node['name'] }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
