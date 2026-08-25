<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource\Pages;

use Filament\Resources\Pages\Page;
use Liberu\Genealogy\TreeViewer\Filament\Resources\TreeViewResource;
use Liberu\Genealogy\TreeViewer\Models\TreeView;
use Liberu\Genealogy\TreeViewer\Queries\TreeGraph as TreeGraphQuery;

final class TreeGraph extends Page
{
    protected static string $resource = TreeViewResource::class;

    protected string $view = 'genealogy-tree-viewer-filament::resources.tree-view-resource.pages.tree-graph';

    public TreeView $record;

    public string $viewMode = 'chart';

    /** @var array<string, mixed> */
    public array $graph = [];

    public function mount(string $record, TreeGraphQuery $treeGraph): void
    {
        $this->record = TreeView::query()->findOrFail($record);
        $this->loadGraph($treeGraph);
    }

    public function setView(string $view, TreeGraphQuery $treeGraph): void
    {
        $this->viewMode = $view;
        $this->loadGraph($treeGraph);
    }

    private function loadGraph(TreeGraphQuery $treeGraph): void
    {
        $this->graph = $this->record->rootPerson === null
            ? []
            : $treeGraph->for($this->record->rootPerson, 4, ! $this->record->is_public, $this->viewMode);
    }
}
