<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-semibold">{{ $record->name }}</h2>
            <select wire:model.live="viewMode" wire:change="setView($event.target.value)">
                <option value="chart">Chart</option>
                <option value="pedigree">Pedigree</option>
                <option value="descendants">Descendants</option>
                <option value="fan">Fan</option>
            </select>
        </div>

        @if ($graph === [])
            <p>No root person is configured for this tree.</p>
        @else
            <p>Root: {{ $graph['root']['name'] }}</p>
            <p>{{ count($graph['nodes']) }} people and {{ count($graph['edges']) }} relationships.</p>
            <ul>
                @foreach ($graph['nodes'] as $node)
                    <li wire:key="tree-graph-node-{{ $node['id'] }}">{{ $node['name'] }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</x-filament-panels::page>
