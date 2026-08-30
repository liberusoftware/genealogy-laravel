<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Livewire;

use Illuminate\Contracts\View\View;
use Liberu\Genealogy\Evidence\Actions\CreateCitationLink;
use Liberu\Genealogy\Evidence\Models\Citation;
use Livewire\Component;

final class CitationLinks extends Component
{
    public string $citationId = '';

    public string $subjectPersonId = '';

    public string $group = 'indi';

    public string $page = '';

    public string $quality = '';

    public string $text = '';

    public function add(CreateCitationLink $create): void
    {
        abort_unless(auth()->check(), 403);
        $this->validate([
            'citationId' => ['required', 'uuid'], 'subjectPersonId' => ['required', 'uuid'],
            'group' => ['required', 'in:indi,indi_name,indi_even,indi_asso,indi_lds'],
            'page' => ['nullable', 'string', 'max:255'], 'quality' => ['nullable', 'string', 'max:255'], 'text' => ['nullable', 'string'],
        ]);
        $create->execute(['citation_id' => $this->citationId, 'subject_person_id' => $this->subjectPersonId, 'group' => $this->group, 'page' => $this->page ?: null, 'quality' => $this->quality ?: null, 'text' => $this->text ?: null]);
        $this->reset('subjectPersonId', 'page', 'quality', 'text');
        $this->dispatch('citation-link-created');
    }

    public function render(): View
    {
        abort_unless(auth()->check(), 403);
        $citation = Citation::query()->with('personLinks.subject')->findOrFail($this->citationId);

        return view('genealogy-evidence-livewire::citation-links', compact('citation'));
    }
}
