<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Research\Models\ChecklistTemplate;
use Liberu\Genealogy\Research\Models\UserChecklist;
use Liberu\Genealogy\Research\Models\UserChecklistItem;

final class CreateChecklist
{
    public function execute(Model $user, string $name, ?ChecklistTemplate $template = null, array $attributes = []): UserChecklist
    {
        $teamId = app(TeamContext::class)->require();

        return DB::transaction(function () use ($user, $name, $template, $attributes, $teamId): UserChecklist {
            $checklist = UserChecklist::query()->create(array_merge($attributes, [
                'team_id' => $teamId,
                'user_id' => $user->getKey(),
                'checklist_template_id' => $template?->getKey(),
                'name' => $name,
            ]));

            if ($template !== null) {
                $template->loadMissing('templateItems');
                foreach ($template->templateItems as $item) {
                    UserChecklistItem::query()->create([
                        'checklist_id' => $checklist->getKey(),
                        'template_item_id' => $item->getKey(),
                        'title' => $item->title,
                        'description' => $item->description,
                        'sort_order' => $item->sort_order,
                        'estimated_time' => $item->estimated_time,
                        'resources' => $item->resources,
                        'tips' => $item->tips,
                    ]);
                }
            }

            return $checklist;
        });
    }
}
