<?php

declare(strict_types=1);

use Liberu\Genealogy\Collaboration\Capability;

it('describes its public capability boundary', function (): void {
    $capability = new Capability('genealogy-collaboration', 'Genealogy Collaboration', ['genealogy.collaboration', 'genealogy.collaboration.lifecycle']);

    expect($capability->name)->toBe('genealogy-collaboration')
        ->and($capability->supports('genealogy.collaboration'))->toBeTrue()
        ->and($capability->supports('unrelated.capability'))->toBeFalse();
});
