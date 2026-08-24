<?php

declare(strict_types=1);

use Liberu\Genealogy\TreeViewer\Capability;

it('describes its public capability boundary', function (): void {
    $capability = new Capability('genealogy-tree-viewer', 'Genealogy Tree Viewer', ['genealogy.tree-viewer', 'genealogy.tree-viewer.lifecycle']);

    expect($capability->name)->toBe('genealogy-tree-viewer')
        ->and($capability->supports('genealogy.tree-viewer'))->toBeTrue()
        ->and($capability->supports('unrelated.capability'))->toBeFalse();
});
