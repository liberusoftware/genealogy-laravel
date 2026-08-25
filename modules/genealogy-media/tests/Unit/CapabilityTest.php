<?php

declare(strict_types=1);

use Liberu\Genealogy\Media\Capability;

it('describes its public capability boundary', function (): void {
    $capability = new Capability('genealogy-media', 'Genealogy Media', ['genealogy.media', 'genealogy.media.lifecycle']);

    expect($capability->name)->toBe('genealogy-media')
        ->and($capability->supports('genealogy.media'))->toBeTrue()
        ->and($capability->supports('unrelated.capability'))->toBeFalse();
});
