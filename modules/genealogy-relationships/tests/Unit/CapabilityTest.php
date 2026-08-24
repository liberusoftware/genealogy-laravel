<?php

declare(strict_types=1);

use Liberu\Genealogy\Relationships\Capability;

it('describes its public capability boundary', function (): void {
    $capability = new Capability('genealogy-relationships', 'Genealogy Relationships', ['genealogy.relationships', 'genealogy.relationships.lifecycle']);

    expect($capability->name)->toBe('genealogy-relationships')
        ->and($capability->supports('genealogy.relationships'))->toBeTrue()
        ->and($capability->supports('unrelated.capability'))->toBeFalse();
});
