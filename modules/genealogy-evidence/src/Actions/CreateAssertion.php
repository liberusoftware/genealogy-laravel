<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Evidence\Actions;

use Liberu\Genealogy\Evidence\Models\Assertion;

final class CreateAssertion
{
    public function __construct(private readonly CreateEvidenceEntity $create) {}

    public function execute(array $attributes): Assertion
    {
        return $this->create->execute(Assertion::class, $attributes);
    }
}
