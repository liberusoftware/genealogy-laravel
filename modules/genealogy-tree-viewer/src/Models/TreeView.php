<?php

declare(strict_types=1);

namespace Liberu\Genealogy\TreeViewer\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Liberu\Genealogy\GenealogyCore\Models\Tree;
use Liberu\Genealogy\People\Models\Person;

final class TreeView extends Tree
{
    public function rootPerson(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'root_person_id');
    }
}
