<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Livewire;

use Liberu\Genealogy\Evidence\Actions\CreateEvidenceRecord;
use Liberu\Genealogy\Evidence\Models\EvidenceRecord;
use Livewire\Component;

final class EvidenceEditor extends Component
{
    public string $name = '';

    public string $kind = 'source';

    public string $repository = '';

    public string $citation = '';

    public string $extract = '';

    public string $assertion = '';

    public string $proofConclusion = '';

    public int $confidence = 0;

    public string $sourceUrl = '';

    public string $eventDate = '';

    public string $subjectPersonId = '';

    public string $status = 'draft';

    public function save(CreateEvidenceRecord $create): void
    {
        abort_unless(auth()->check(), 403);
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'kind' => ['required', 'in:'.implode(',', EvidenceRecord::KINDS)],
            'repository' => ['nullable', 'string', 'max:255'],
            'citation' => ['nullable', 'string', 'max:10000'],
            'extract' => ['nullable', 'string', 'max:10000'],
            'assertion' => ['nullable', 'string', 'max:10000'],
            'proofConclusion' => ['nullable', 'string', 'max:10000'],
            'confidence' => ['integer', 'min:0', 'max:100'],
            'sourceUrl' => ['nullable', 'url', 'max:2048'],
            'eventDate' => ['nullable', 'date'],
            'subjectPersonId' => ['nullable', 'uuid'],
            'status' => ['required', 'in:'.implode(',', EvidenceRecord::STATUSES)],
        ]);
        $create->execute([
            'name' => $this->name,
            'kind' => $this->kind,
            'repository' => $this->repository ?: null,
            'citation' => $this->citation ?: null,
            'extract' => $this->extract ?: null,
            'assertion' => $this->assertion ?: null,
            'proof_conclusion' => $this->proofConclusion ?: null,
            'confidence' => $this->confidence,
            'source_url' => $this->sourceUrl ?: null,
            'event_date' => $this->eventDate ?: null,
            'subject_person_id' => $this->subjectPersonId ?: null,
            'status' => $this->status,
        ]);
        $this->reset();
        $this->dispatch('evidence-record-created');
    }

    public function render(): mixed
    {
        abort_unless(auth()->check(), 403);

        return view('genealogy-evidence-livewire::editor', ['kinds' => EvidenceRecord::KINDS]);
    }
}
