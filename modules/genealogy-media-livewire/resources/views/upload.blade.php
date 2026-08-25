<div>
    <input type="file" wire:model="file" accept="image/*,audio/*,video/*,.pdf,.txt,.doc,.docx">
    <input type="text" wire:model="name" placeholder="Display name">
    <select wire:model="kind">
        @foreach (\Liberu\Genealogy\Media\Models\MediaAsset::KINDS as $option)
            <option value="{{ $option }}">{{ ucfirst($option) }}</option>
        @endforeach
    </select>
    <select wire:model="rightsStatus">
        @foreach (\Liberu\Genealogy\Media\Models\MediaAsset::RIGHTS_STATUSES as $option)
            <option value="{{ $option }}">{{ ucwords(str_replace('_', ' ', $option)) }}</option>
        @endforeach
    </select>
    <button type="button" wire:click="save" wire:loading.attr="disabled">Upload</button>
</div>
