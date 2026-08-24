<?php

declare(strict_types=1);

use Liberu\Genealogy\People\Capability;

it('describes its public capability boundary', function (): void {
    $capability = new Capability('genealogy-people', 'Genealogy People', ['genealogy.people', 'genealogy.people.lifecycle']);

    expect($capability->name)->toBe('genealogy-people')
        ->and($capability->supports('genealogy.people'))->toBeTrue()
        ->and($capability->supports('unrelated.capability'))->toBeFalse();
});
