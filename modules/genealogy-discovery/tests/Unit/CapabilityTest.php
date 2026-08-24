<?php

declare(strict_types=1);

use Liberu\Genealogy\Discovery\Capability;

it('describes its public capability boundary', function (): void {
    $capability = new Capability('genealogy-discovery', 'Genealogy Discovery', ['genealogy.discovery', 'genealogy.discovery.lifecycle']);

    expect($capability->name)->toBe('genealogy-discovery')
        ->and($capability->supports('genealogy.discovery'))->toBeTrue()
        ->and($capability->supports('unrelated.capability'))->toBeFalse();
});
