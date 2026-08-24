<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Actions;

use Illuminate\Support\Arr;
use Liberu\Genealogy\TreeViewer\Models\TreeView;

final class CreateTreeView
{
    public function execute(array $attributes): TreeView
    {
        return TreeView::query()->create(Arr::only($attributes, ['name', 'status', 'metadata']));
    }
}
