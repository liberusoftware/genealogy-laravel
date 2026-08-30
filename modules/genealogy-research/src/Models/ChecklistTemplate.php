<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class ChecklistTemplate extends Model
{
    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_checklist_templates';

    protected $fillable = ['team_id', 'created_by', 'name', 'description', 'category', 'is_public', 'is_default', 'tags', 'difficulty_level', 'estimated_time'];

    protected function casts(): array
    {
        return ['is_public' => 'boolean', 'is_default' => 'boolean', 'tags' => 'array', 'estimated_time' => 'integer'];
    }

    public function templateItems(): HasMany
    {
        return $this->hasMany(ChecklistTemplateItem::class, 'checklist_template_id')->orderBy('sort_order');
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(UserChecklist::class, 'checklist_template_id');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }
}
