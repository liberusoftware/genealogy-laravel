<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\ImportExport\Actions\CreateDataTransfer;
use Liberu\Genealogy\ImportExport\Actions\UndoDataTransfer;
use Liberu\Genealogy\ImportExport\Exporters\GedcomExporter;
use Liberu\Genealogy\ImportExport\Importers\GenealogyDocumentParser;
use Liberu\Genealogy\ImportExport\Importers\GenealogyImportService;
use Liberu\Genealogy\ImportExport\Models\DataTransfer;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Models\Relationship;

uses(RefreshDatabase::class);

it('validates and records tenant-owned transfer metadata', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    $transfer = (new CreateDataTransfer())->execute([
        'name' => 'Family archive', 'format' => 'gedcom', 'direction' => 'import', 'status' => 'active',
    ]);

    expect($transfer)->toBeInstanceOf(DataTransfer::class)
        ->and($transfer->team_id)->toBe((string) $team->id);
});

it('parses GEDCOM and reports malformed document errors without importing it', function (): void {
    $parsed = (new GenealogyDocumentParser())->parse("0 HEAD\n1 SOUR Test\n0 @I1@ INDI\n1 NAME Ada /Lovelace/\n0 TRLR\n");

    expect($parsed['format'])->toBe('gedcom')
        ->and($parsed['people'][0]['given_name'])->toBe('Ada')
        ->and($parsed['people'][0]['family_name'])->toBe('Lovelace')
        ->and((new GenealogyDocumentParser())->parse('not GEDCOM')['errors'])->not->toBeEmpty();
});

it('maps alternate names and life events through the import boundary', function (): void {
    $team = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($team->id);
    $content = implode("\n", [
        '0 HEAD',
        '0 @I1@ INDI',
        '1 NAME Ada /Lovelace/',
        '1 NAME Augusta /King/',
        '1 BIRT',
        '2 DATE 10 DEC 1815',
        '2 PLAC London',
        '1 OCCU Mathematician',
        '0 TRLR',
    ]);

    $result = app(GenealogyImportService::class)->import($content, false);
    $person = Person::query()->firstOrFail();

    expect($result['created'])->toBe(1)
        ->and($person->names()->count())->toBe(1)
        ->and($person->lifeEvents()->where('type', 'birth')->where('place', 'London')->exists())->toBeTrue()
        ->and($person->lifeEvents()->where('type', 'occu')->exists())->toBeTrue();
});

it('round trips GEDCOM people and relationship edges without duplicate creation', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $content = implode("\n", [
        '0 HEAD',
        '0 @I1@ INDI', '1 NAME Parent /Example/',
        '0 @I2@ INDI', '1 NAME Child /Example/',
        '0 @F1@ FAM', '1 HUSB @I1@', '1 CHIL @I2@',
        '0 TRLR',
    ]);
    $service = app(GenealogyImportService::class);

    expect($service->preview($content)['relationships'])->toBe(1);
    $first = $service->import($content, false);
    $second = $service->import($content, false);

    expect($first['created'])->toBe(2)
        ->and($first['relationships_created'])->toBe(1)
        ->and($second['created'])->toBe(0)
        ->and($second['updated'])->toBe(2)
        ->and($second['relationships_created'])->toBe(0)
        ->and(Person::query()->count())->toBe(2)
        ->and(Relationship::query()->count())->toBe(1);

    $gedcom = app(GedcomExporter::class)->export(Person::query()->get(), Relationship::query()->get());
    expect($gedcom)->toContain('0 @I1@ INDI')->toContain('1 HUSB')->toContain('1 NAME Parent /Example/');
});

it('supports an audited undo window for completed imports', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $transfer = app(CreateDataTransfer::class)->execute([
        'name' => 'Undoable archive',
        'format' => 'gedcom',
        'direction' => 'import',
        'status' => 'active',
    ]);
    $content = implode("\n", [
        '0 HEAD',
        '0 @I1@ INDI', '1 NAME Parent /Example/',
        '0 @I2@ INDI', '1 NAME Child /Example/',
        '0 @F1@ FAM', '1 HUSB @I1@', '1 CHIL @I2@',
        '0 TRLR',
    ]);

    app(GenealogyImportService::class)->import($content, false, $transfer);
    $transfer->refresh();

    expect($transfer->status)->toBe('completed')
        ->and($transfer->metadata['undo']['created_people'])->toHaveCount(2)
        ->and(Relationship::query()->count())->toBe(1);

    $undone = app(UndoDataTransfer::class)->execute($transfer);

    expect($undone->status)->toBe('rolled_back')
        ->and(Person::withTrashed()->count())->toBe(0)
        ->and(Relationship::query()->count())->toBe(0);
});
