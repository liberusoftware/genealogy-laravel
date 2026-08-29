<form wire:submit="save" aria-label="Collaboration proposal">
    <label>Title <input wire:model="title" type="text" required maxlength="255"></label>
    <label>Description <textarea wire:model="description"></textarea></label>
    @error('title') <p role="alert">{{ $message }}</p> @enderror
    @error('description') <p role="alert">{{ $message }}</p> @enderror
    <button type="submit" wire:loading.attr="disabled">Save proposal</button>
    @if ($proposal)
        <button type="button" wire:click="review('in_review')">Send for review</button>
        <button type="button" wire:click="review('approved')">Approve</button>
        <button type="button" wire:click="review('rejected')">Reject</button>
    @endif
</form>
