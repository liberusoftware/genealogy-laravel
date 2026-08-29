<div>
    <form wire:submit="invite" aria-label="Invite collaboration member">
        <label for="genealogy-collaboration-invitation-email">Email</label>
        <input id="genealogy-collaboration-invitation-email" type="email" wire:model="email" required>
        <label for="genealogy-collaboration-invitation-role">Role</label>
        <select id="genealogy-collaboration-invitation-role" wire:model="role">
            @foreach (['viewer', 'contributor', 'reviewer', 'editor', 'owner'] as $availableRole)
                <option value="{{ $availableRole }}">{{ ucfirst($availableRole) }}</option>
            @endforeach
        </select>
        @error('email') <p role="alert">{{ $message }}</p> @enderror
        @error('role') <p role="alert">{{ $message }}</p> @enderror
        <button type="submit" wire:loading.attr="disabled">Invite</button>
    </form>
    <label for="genealogy-collaboration-invitation-status">Invitation status</label>
    <select id="genealogy-collaboration-invitation-status" wire:model.live="status">
        <option value="">All</option><option value="pending">Pending</option><option value="accepted">Accepted</option><option value="revoked">Revoked</option>
    </select>
    <ul>
        @foreach ($records as $record)
            <li wire:key="genealogy-collaboration-invitation-{{ $record->id }}">
                {{ $record->email }} <span>{{ $record->role }} ({{ $record->status }})</span>
                @if ($record->status === 'pending')
                    <button type="button" wire:click="revoke('{{ $record->id }}')">Revoke</button>
                    @if (auth()->user()?->email === $record->email)
                        <button type="button" wire:click="accept('{{ $record->id }}')">Accept</button>
                    @endif
                @endif
            </li>
        @endforeach
    </ul>
</div>
