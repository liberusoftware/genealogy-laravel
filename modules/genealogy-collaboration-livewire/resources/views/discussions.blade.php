<div>
    <form wire:submit="post">
        <label for="genealogy-collaboration-discussion-body">Discussion message</label>
        <textarea id="genealogy-collaboration-discussion-body" wire:model="body"></textarea>
        @error('body') <p role="alert">{{ $message }}</p> @enderror
        <button type="submit">Post</button>
    </form>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-collaboration-discussion-{{ $record->id }}">{{ $record->body }}</li>
        @endforeach
    </ul>
</div>
