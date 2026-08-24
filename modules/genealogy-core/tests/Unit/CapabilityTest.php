<?php

declare(strict_types=1);

use Liberu\Genealogy\GenealogyCore\Capability;

it('describes its public capability boundary', function (): void {
    $capability = new Capability('genealogy-core', 'Genealogy Genealogy Core', ['genealogy.genealogy-core', 'genealogy.genealogy-core.lifecycle']);

    expect($capability->name)->toBe('genealogy-core')
        ->and($capability->supports('genealogy.genealogy-core'))->toBeTrue()
        ->and($capability->supports('unrelated.capability'))->toBeFalse();
});
