<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ChecklistTemplateItem extends Model
{
    use HasUuids;

    protected $table = 'genealogy_checklist_template_items';

    protected $fillable = ['checklist_template_id', 'title', 'description', 'sort_order', 'category', 'is_required', 'estimated_time', 'resources', 'tips'];

    protected function casts(): array
    {
        return ['is_required' => 'boolean', 'estimated_time' => 'integer', 'resources' => 'array', 'tips' => 'array'];
    }

    public function checklistTemplate(): BelongsTo
    {
        return $this->belongsTo(ChecklistTemplate::class, 'checklist_template_id');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(UserChecklistItem::class, 'template_item_id');
    }

    public function scopeRequired(Builder $query): Builder
    {
        return $query->where('is_required', true);
    }
}
