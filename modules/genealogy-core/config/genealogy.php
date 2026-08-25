<?php

declare(strict_types=1);

return [
    'team_model' => 'Liberu\\Foundation\\Organizations\\Models\\Team',

    // These models remain configurable so the core package does not need to
    // depend on the people or application modules just to expose relations.
    'person_model' => 'Liberu\\Genealogy\\People\\Models\\Person',
    'user_model' => null,
];
