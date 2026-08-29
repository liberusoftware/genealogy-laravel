<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Liberu\Genealogy\Evidence\Actions\CreateEvidenceEntity;
use Liberu\Genealogy\Evidence\Models\Assertion;
use Liberu\Genealogy\Evidence\Models\Citation;
use Liberu\Genealogy\Evidence\Models\Extract;
use Liberu\Genealogy\Evidence\Models\ProofConclusion;
use Liberu\Genealogy\Evidence\Models\Repository;
use Liberu\Genealogy\Evidence\Models\Source;
use Livewire\Component;

final class EvidenceEntityEditor extends Component
{
    public string $entity = 'sources';

    public string $name = '';

    public string $title = '';

    public string $content = '';

    public string $statement = '';

    public string $conclusion = '';

    public string $sourceId = '';

    public string $citationId = '';

    public string $assertionId = '';

    public int $confidence = 0;

    public function save(CreateEvidenceEntity $create): void
    {
        abort_unless(auth()->check(), 403);
        $values = match ($this->entity) {
            'sources' => ['name' => $this->name],
            'repositories' => ['name' => $this->name],
            'citations' => ['source_id' => $this->sourceId, 'title' => $this->title, 'confidence' => $this->confidence],
            'extracts' => ['citation_id' => $this->citationId, 'content' => $this->content],
            'assertions' => ['statement' => $this->statement, 'citation_id' => $this->citationId, 'confidence' => $this->confidence],
            'proof-conclusions' => ['assertion_id' => $this->assertionId, 'conclusion' => $this->conclusion, 'confidence' => $this->confidence],
            default => abort(404),
        };
        $this->validate(['confidence' => ['integer', 'between:0,100']]);
        $create->execute($this->modelClass(), $values);
        $this->reset('name', 'title', 'content', 'statement', 'conclusion', 'sourceId', 'citationId', 'assertionId');
        $this->dispatch('evidence-entity-created', entity: $this->entity);
    }

    public function render(): View
    {
        abort_unless(auth()->check(), 403);

        return view('genealogy-evidence-livewire::entity-editor');
    }

    /** @return class-string<Model> */
    private function modelClass(): string
    {
        return match ($this->entity) {
            'sources' => Source::class,
            'repositories' => Repository::class,
            'citations' => Citation::class,
            'extracts' => Extract::class,
            'assertions' => Assertion::class,
            'proof-conclusions' => ProofConclusion::class,
            default => abort(404),
        };
    }
}
