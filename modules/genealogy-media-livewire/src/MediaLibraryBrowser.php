<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Livewire;

use Liberu\Genealogy\Media\Models\MediaAsset;
use Liberu\Genealogy\Media\Queries\MediaLibrary;
use Livewire\Component;

final class MediaLibraryBrowser extends Component
{
    public string $kind = '';

    public string $term = '';

    public bool $publicOnly = false;

    public function render(MediaLibrary $library): mixed
    {
        $this->validate(['kind' => ['nullable', 'in:'.implode(',', MediaAsset::KINDS)], 'term' => ['nullable', 'string', 'max:200'], 'publicOnly' => ['boolean']]);

        return view('genealogy-media-livewire::library', ['assets' => $library->execute($this->kind !== '' ? $this->kind : null, $this->term, $this->publicOnly)]);
    }
}
