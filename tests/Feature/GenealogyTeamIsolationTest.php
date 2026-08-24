<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Models\Person;

uses(RefreshDatabase::class);

it('scopes genealogy records to the established team and stamps new rows', function (): void {
    $firstTeam = Team::factory()->create();
    $secondTeam = Team::factory()->create();
    $context = app(TeamContext::class);

    $context->set($firstTeam->getKey());
    $firstPerson = Person::query()->create(['given_name' => 'First team']);

    $context->set($secondTeam->getKey());
    $secondPerson = Person::query()->create(['given_name' => 'Second team']);

    expect($firstPerson->team_id)->toBe((string) $firstTeam->getKey())
        ->and($secondPerson->team_id)->toBe((string) $secondTeam->getKey())
        ->and(Person::query()->pluck('given_name')->all())->toBe(['Second team']);

    $context->set($firstTeam->getKey());
    expect(Person::query()->pluck('given_name')->all())->toBe(['First team']);
});

it('fails closed when a genealogy query has no team context', function (): void {
    expect(Person::query()->count())->toBe(0)
        ->and(fn (): Person => Person::query()->create(['given_name' => 'Unscoped']))
        ->toThrow(LogicException::class);
});

it('restores the previous context after scoped work', function (): void {
    $context = app(TeamContext::class);
    $context->set('outer-team');

    $result = $context->run('inner-team', function () use ($context): string {
        expect($context->require())->toBe('inner-team');

        return 'completed';
    });

    expect($result)->toBe('completed')
        ->and($context->require())->toBe('outer-team');
});
