<?php

declare(strict_types=1);

use Liberu\Genealogy\Places\Capability;

it('describes its public capability boundary', function (): void {
    $capability = new Capability('genealogy-places', 'Genealogy Places', ['genealogy.places', 'genealogy.places.lifecycle']);

    expect($capability->name)->toBe('genealogy-places')
        ->and($capability->supports('genealogy.places'))->toBeTrue()
        ->and($capability->supports('unrelated.capability'))->toBeFalse();
});
