<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

it('keeps each Liberu API OpenAPI contract aligned with its package boundary', function (string $package, string $collectionPath): void {
    $document = Yaml::parseFile(base_path("modules/{$package}/resources/openapi.yaml"));

    expect($document['openapi'])->toBe('3.1.0')
        ->and($document['security'][0]['sanctum'])->toBe([])
        ->and($document['paths'])->toHaveKeys([$collectionPath, "{$collectionPath}/{record}"])
        ->and($document['components']['parameters']['IdempotencyKey']['required'])->toBeTrue()
        ->and($document['components']['parameters']['PageSize']['schema']['maximum'])->toBe(100);
})->with([
    ['liberu-business-workflow-reconciliation-api', '/business-workflow-reconciliation'],
    ['liberu-executive-insights-api', '/executive-insights'],
    ['liberu-platform-orchestration-api', '/platform-orchestration'],
    ['liberu-revenue-and-care-orchestration-api', '/revenue-and-care-orchestration'],
]);
