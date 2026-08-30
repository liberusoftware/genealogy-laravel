<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class UserChecklist extends Model
{
    public const STATUSES = ['not_started', 'in_progress', 'completed', 'on_hold'];

    public const PRIORITIES = ['low', 'medium', 'high', 'urgent'];

    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_checklists';

    protected $fillable = ['team_id', 'user_id', 'checklist_template_id', 'name', 'description', 'subject_type', 'subject_id', 'status', 'started_at', 'completed_at', 'notes', 'priority', 'due_date'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'completed_at' => 'datetime', 'due_date' => 'date'];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'checklist_template_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(UserChecklistItem::class, 'checklist_id')->orderBy('sort_order');
    }

    public function completedItems(): HasMany
    {
        return $this->items()->where('is_completed', true);
    }

    public function pendingItems(): HasMany
    {
        return $this->items()->where('is_completed', false);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function getCompletionPercentageAttribute(): float
    {
        $total = $this->items()->count();

        return $total === 0 ? 0.0 : round(($this->completedItems()->count() / $total) * 100, 2);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date !== null && $this->due_date->isPast() && $this->status !== 'completed';
    }

    public function markAsStarted(): void
    {
        $this->update(['status' => 'in_progress', 'started_at' => $this->started_at ?? now()]);
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed', 'completed_at' => now()]);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['not_started', 'in_progress']);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', now())->where('status', '!=', 'completed');
    }
}
