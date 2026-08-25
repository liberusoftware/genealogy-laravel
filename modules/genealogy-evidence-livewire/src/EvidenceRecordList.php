<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Livewire;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Liberu\Genealogy\Evidence\Actions\ArchiveEvidenceRecord;
use Liberu\Genealogy\Evidence\Actions\ReviewEvidenceRecord;
use Liberu\Genealogy\Evidence\Models\EvidenceRecord;
use Livewire\Component;

final class EvidenceRecordList extends Component
{
    public string $status = '';

    public function review(string $recordId, ReviewEvidenceRecord $review): void
    {
        abort_unless(auth()->check(), 403);
        Validator::make(['record_id' => $recordId], ['record_id' => ['required', 'uuid']])->validate();
        $this->validate(['status' => ['nullable', Rule::in(EvidenceRecord::STATUSES)]]);
        $record = EvidenceRecord::query()->findOrFail($recordId);
        $review->execute($record);
        $this->dispatch('evidence-record-reviewed', recordId: $recordId);
    }

    public function archive(string $recordId, ArchiveEvidenceRecord $archive): void
    {
        abort_unless(auth()->check(), 403);
        Validator::make(['record_id' => $recordId], ['record_id' => ['required', 'uuid']])->validate();
        $this->validate(['status' => ['nullable', Rule::in(EvidenceRecord::STATUSES)]]);
        $record = EvidenceRecord::query()->findOrFail($recordId);
        $archive->execute($record);
        $this->dispatch('evidence-record-archived', recordId: $recordId);
    }

    public function render(): mixed
    {
        return view('genealogy-evidence-livewire::list', [
            'records' => EvidenceRecord::query()
                ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
