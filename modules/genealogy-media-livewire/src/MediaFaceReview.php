<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Media\Livewire;

use Liberu\Genealogy\Media\Actions\ReviewMediaFaceTag;
use Liberu\Genealogy\Media\Models\MediaFaceTag;
use Livewire\Component;

final class MediaFaceReview extends Component
{
    /** @var list<array{id: string, media_asset_id: string, confidence: ?string, bounding_box: array<string, mixed>, person_id: ?string}> */
    public array $pendingTags = [];

    public int $currentTagIndex = 0;

    public ?string $selectedPersonId = null;

    public function mount(): void
    {
        abort_unless(auth()->check(), 403);
        $this->loadPendingTags();
    }

    public function loadPendingTags(): void
    {
        $this->pendingTags = MediaFaceTag::query()
            ->where('status', 'pending')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (MediaFaceTag $tag): array => [
                'id' => (string) $tag->getKey(),
                'media_asset_id' => (string) $tag->media_asset_id,
                'confidence' => $tag->confidence !== null ? (string) $tag->confidence : null,
                'bounding_box' => $tag->bounding_box ?? [],
                'person_id' => $tag->person_id,
            ])
            ->values()
            ->all();

        $this->currentTagIndex = 0;
        $this->selectedPersonId = $this->pendingTags[0]['person_id'] ?? null;
    }

    public function confirm(ReviewMediaFaceTag $review): void
    {
        $this->reviewCurrentTag($review, 'confirmed');
    }

    public function reject(ReviewMediaFaceTag $review): void
    {
        $this->reviewCurrentTag($review, 'rejected');
    }

    public function skip(): void
    {
        $this->nextTag();
    }

    public function previous(): void
    {
        if ($this->currentTagIndex === 0) {
            return;
        }

        $this->currentTagIndex--;
        $this->selectedPersonId = $this->pendingTags[$this->currentTagIndex]['person_id'] ?? null;
    }

    /** @return array{id: string, media_asset_id: string, confidence: ?string, bounding_box: array<string, mixed>, person_id: ?string}|null */
    public function currentTag(): ?array
    {
        return $this->pendingTags[$this->currentTagIndex] ?? null;
    }

    public function render(): mixed
    {
        return view('genealogy-media-livewire::face-review', [
            'currentTag' => $this->currentTag(),
            'totalTags' => count($this->pendingTags),
        ]);
    }

    private function reviewCurrentTag(ReviewMediaFaceTag $review, string $status): void
    {
        abort_unless(auth()->check(), 403);
        $this->validate(['selectedPersonId' => ['nullable', 'uuid']]);
        $tag = $this->currentTag();
        if ($tag === null) {
            return;
        }

        $record = MediaFaceTag::query()->whereKey($tag['id'])->where('status', 'pending')->first();
        if ($record === null) {
            $this->addError('review', 'The face tag is no longer available for review.');

            return;
        }

        $review->execute($record, $status, $status === 'confirmed' ? $this->selectedPersonId : null, auth()->id() ? (string) auth()->id() : null);
        $this->nextTag();
    }

    private function nextTag(): void
    {
        if ($this->currentTagIndex < count($this->pendingTags) - 1) {
            $this->currentTagIndex++;
            $this->selectedPersonId = $this->pendingTags[$this->currentTagIndex]['person_id'] ?? null;

            return;
        }

        $this->loadPendingTags();
    }
}
