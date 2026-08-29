<div>
    <label for="genealogy-import-file">Genealogy file</label>
    <input id="genealogy-import-file" type="file" wire:model="file" accept=".ged,.gedcom,.xml,.txt">
    @error('file') <p role="alert">{{ $message }}</p> @enderror
    <div wire:loading wire:target="file,preview,import" role="status">Processing…</div>
    <button type="button" wire:click="preview" wire:loading.attr="disabled" wire:target="preview">Preview</button>
    <button type="button" wire:click="import" wire:loading.attr="disabled" wire:target="import">Import</button>
    @if ($transferId)
        <button type="button" wire:click="undo" wire:loading.attr="disabled" wire:target="undo">Undo import</button>
    @endif
    @if ($report)
        <dl>
            <dt>Format</dt><dd>{{ $report['format'] }}</dd>
            <dt>People</dt><dd>{{ $report['people'] }}</dd>
            <dt>Families</dt><dd>{{ $report['families'] }}</dd>
            <dt>Duplicates</dt><dd>{{ $report['duplicates'] }}</dd>
        </dl>
    @endif
</div>
