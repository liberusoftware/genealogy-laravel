<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\ImportExport\Exporters\GedcomExporter;
use Liberu\Genealogy\ImportExport\Exporters\GedcomXExporter;
use Liberu\Genealogy\People\Actions\CreatePerson;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Actions\CreateRelationship;
use Liberu\Genealogy\Relationships\Models\Relationship;

uses(RefreshDatabase::class);

function exportTeam(): void
{
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
}

it('exports a GEDCOM 7 document without removed 5.5.1 header structures', function (): void {
    exportTeam();
    $person = app(CreatePerson::class)->execute([
        'given_name' => 'Alex', 'family_name' => 'Morgan', 'sex' => 'X',
    ]);

    $content = app(GedcomExporter::class)->export(Person::query()->get(), null, '7.0');

    expect($content)
        ->toStartWith("0 HEAD\n1 SOUR Genealogy\n1 GEDC\n2 VERS 7.0")
        ->toContain('0 @I'.$person->id.'@ INDI')
        ->toContain('1 SEX X')
        ->not->toContain('1 CHAR')
        ->not->toContain('2 FORM')
        ->not->toContain('CONC');
});

it('exports GEDCOM X persons, facts, and tenant-scoped relationships', function (): void {
    exportTeam();
    $parent = app(CreatePerson::class)->execute([
        'given_name' => 'Ada', 'family_name' => 'Lovelace', 'sex' => 'F', 'birth_date' => '1815-12-10',
    ]);
    $child = app(CreatePerson::class)->execute([
        'given_name' => 'Byron', 'family_name' => 'King', 'sex' => 'M', 'birth_date' => '1836-11-27',
    ]);
    app(CreateRelationship::class)->execute([
        'person_id' => $parent->id, 'related_person_id' => $child->id, 'type' => 'parent',
    ]);

    $data = json_decode(app(GedcomXExporter::class)->export(
        Person::query()->get(),
        Relationship::query()->get(),
    ), true, flags: JSON_THROW_ON_ERROR);

    expect($data['persons'])->toHaveCount(2)
        ->and($data['persons'][0]['names'][0]['nameForms'][0]['parts'])->toContain(['type' => 'http://gedcomx.org/Given', 'value' => 'Ada'])
        ->and($data['persons'][0]['facts'][0]['type'])->toBe('http://gedcomx.org/Birth')
        ->and($data['relationships'][0]['type'])->toBe('http://gedcomx.org/ParentChild')
        ->and($data['relationships'][0]['person1']['resource'])->toBe('#p'.$parent->id)
        ->and($data['relationships'][0]['person2']['resource'])->toBe('#p'.$child->id);
});
