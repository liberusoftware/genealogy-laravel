<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Livewire;

use Liberu\Genealogy\Media\Actions\StoreMediaUpload;
use Liberu\Genealogy\Media\Models\MediaAsset;
use Livewire\Component;
use Livewire\WithFileUploads;

final class MediaAssetUpload extends Component
{
    use WithFileUploads;

    public mixed $file = null;

    public string $name = '';

    public string $kind = 'document';

    public string $rightsStatus = 'unknown';

    public function save(StoreMediaUpload $upload): void
    {
        abort_unless(auth()->check(), 403);
        $this->validate([
            'file' => ['required', 'file', 'max:51200'],
            'name' => ['nullable', 'string', 'max:255'],
            'kind' => ['required', 'in:'.implode(',', MediaAsset::KINDS)],
            'rightsStatus' => ['required', 'in:'.implode(',', MediaAsset::RIGHTS_STATUSES)],
        ]);
        $upload->execute($this->file, [
            'name' => $this->name !== '' ? $this->name : null,
            'kind' => $this->kind,
            'rights_status' => $this->rightsStatus,
        ]);
        $this->reset(['file', 'name']);
        $this->dispatch('genealogy-media-uploaded');
    }

    public function render(): mixed
    {
        return view('genealogy-media-livewire::upload');
    }
}
