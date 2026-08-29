<div>
    <label for="genealogy-tree-viewer-list-search">Search</label>
    <input id="genealogy-tree-viewer-list-search" type="search" wire:model.live.debounce.300ms="search">
    <label for="genealogy-tree-viewer-list-status">Status</label>
    <select id="genealogy-tree-viewer-list-status" wire:model.live="status">
        <option value="">All</option>
        @foreach (\Liberu\Genealogy\TreeViewer\Models\TreeView::STATUSES as $treeStatus)
            <option value="{{ $treeStatus }}">{{ ucfirst($treeStatus) }}</option>
        @endforeach
    </select>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-tree-viewer-list-{{ $record->id }}">{{ $record->name }}</li>
        @endforeach
    </ul>
</div>
