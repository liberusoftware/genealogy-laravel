<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Research\Actions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\Research\Events\ResearchProjectCreated;
use Liberu\Genealogy\Research\Models\ResearchProject;

final class CreateResearchProject
{
    public function execute(array $attributes): ResearchProject
    {
        $values = Arr::only($attributes, ['name', 'status', 'metadata']);
        $this->validate($values);
        $values['team_id'] = app(TeamContext::class)->require();

        $project = DB::transaction(fn (): ResearchProject => ResearchProject::query()->create($values));
        event(new ResearchProjectCreated($project));

        return $project;
    }

    /** @param array<string, mixed> $values */
    public function validate(array $values): void
    {
        if (trim((string) ($values['name'] ?? '')) === '') {
            throw ValidationException::withMessages(['name' => 'A research project name is required.']);
        }
        if (isset($values['status']) && ! in_array($values['status'], ResearchProject::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'The research project status is not supported.']);
        }
    }
}
