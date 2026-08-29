<form wire:submit="save">
    <input type="text" wire:model="name" placeholder="Name" aria-label="Name">
    <input type="text" wire:model="title" placeholder="Title" aria-label="Title">
    <textarea wire:model="content" placeholder="Content" aria-label="Content"></textarea>
    <textarea wire:model="statement" placeholder="Statement" aria-label="Statement"></textarea>
    <textarea wire:model="conclusion" placeholder="Conclusion" aria-label="Conclusion"></textarea>
    <input type="number" wire:model="confidence" min="0" max="100" aria-label="Confidence">
    <button type="submit">Save</button>
</form>
