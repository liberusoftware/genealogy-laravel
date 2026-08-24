<?php

declare(strict_types=1);

use Liberu\Genealogy\Timeline\Capability;

it('describes its public capability boundary', function (): void {
    $capability = new Capability('genealogy-timeline', 'Genealogy Timeline', ['genealogy.timeline', 'genealogy.timeline.lifecycle']);

    expect($capability->name)->toBe('genealogy-timeline')
        ->and($capability->supports('genealogy.timeline'))->toBeTrue()
        ->and($capability->supports('unrelated.capability'))->toBeFalse();
});
