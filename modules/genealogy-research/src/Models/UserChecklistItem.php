<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class UserChecklistItem extends Model
{
    use HasUuids;

    protected $table = 'genealogy_checklist_items';

    protected $fillable = ['checklist_id', 'template_item_id', 'title', 'description', 'sort_order', 'is_completed', 'completed_at', 'notes', 'estimated_time', 'actual_time', 'resources', 'tips'];

    protected function casts(): array
    {
        return ['is_completed' => 'boolean', 'completed_at' => 'datetime', 'estimated_time' => 'integer', 'actual_time' => 'integer', 'resources' => 'array', 'tips' => 'array'];
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(UserChecklist::class, 'checklist_id');
    }

    public function templateItem(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplateItem::class, 'template_item_id');
    }

    public function markAsCompleted(?int $actualTime = null): void
    {
        $this->update(['is_completed' => true, 'completed_at' => now(), 'actual_time' => $actualTime]);
        $checklist = $this->checklist()->firstOrFail();
        if ($checklist->pendingItems()->count() === 0) {
            $checklist->markAsCompleted();
        } elseif ($checklist->status === 'not_started') {
            $checklist->markAsStarted();
        }
    }

    public function markAsIncomplete(): void
    {
        $this->update(['is_completed' => false, 'completed_at' => null, 'actual_time' => null]);
        $checklist = $this->checklist()->firstOrFail();
        if ($checklist->status === 'completed') {
            $checklist->update(['status' => 'in_progress', 'completed_at' => null]);
        }
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('is_completed', true);
    }
}
