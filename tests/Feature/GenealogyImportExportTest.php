<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\ImportExport\Actions\CreateDataTransfer;
use Liberu\Genealogy\ImportExport\Actions\ExportGenealogyData;
use Liberu\Genealogy\ImportExport\Actions\UndoDataTransfer;
use Liberu\Genealogy\ImportExport\Exporters\GedcomExporter;
use Liberu\Genealogy\ImportExport\Importers\GenealogyDocumentParser;
use Liberu\Genealogy\ImportExport\Importers\GenealogyImportService;
use Liberu\Genealogy\ImportExport\Models\DataTransfer;
use Liberu\Genealogy\People\Actions\CreatePerson;
use Liberu\Genealogy\People\Models\Person;
use Liberu\Genealogy\Relationships\Models\Relationship;
use Livewire\Livewire;

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

it('preserves GEDCOM family marriage and divorce events through import and export', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $content = implode("\n", [
        '0 HEAD',
        '0 @I1@ INDI', '1 NAME Ada /Example/',
        '0 @I2@ INDI', '1 NAME Charles /Example/',
        '0 @F1@ FAM', '1 HUSB @I1@', '1 WIFE @I2@',
        '1 MARR', '2 DATE 10 DEC 1843', '2 PLAC London',
        '1 DIV', '2 DATE 01 JAN 1850', '2 NOTE Civil record',
        '0 TRLR',
    ]);

    $parsed = app(GenealogyDocumentParser::class)->parse($content);
    expect($parsed['families'][0]['events'])->toBe([
        ['type' => 'marriage', 'date' => '1843-12-10', 'place' => 'London', 'description' => null],
        ['type' => 'divorce', 'date' => '1850-01-01', 'place' => null, 'description' => 'Civil record'],
    ]);

    app(GenealogyImportService::class)->import($content, false);
    $gedcom = app(GedcomExporter::class)->export(Person::query()->get(), Relationship::query()->get());

    expect($gedcom)->toContain("1 MARR\n2 DATE 1843-12-10\n2 PLAC London")
        ->toContain("1 DIV\n2 DATE 1850-01-01\n2 NOTE Civil record");
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
        ->and(Relationship::query()->count())->toBe(0)
        ->and(DB::table('activity_log')->where('event', 'data_transfer_undone')->exists())->toBeTrue();
});

it('records a failed transfer when validation rejects an import', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $transfer = app(CreateDataTransfer::class)->execute([
        'name' => 'Invalid archive',
        'format' => 'gedcom',
        'direction' => 'import',
        'status' => 'active',
    ]);

    expect(fn () => app(GenealogyImportService::class)->import('not a genealogy document', false, $transfer))
        ->toThrow(InvalidArgumentException::class);

    expect($transfer->refresh()->status)->toBe('failed')
        ->and($transfer->metadata['failure']['message'])->toContain('invalid records');
});

it('exports through the audited domain boundary for both supported formats', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $person = (new CreatePerson())->execute(['given_name' => 'Ada', 'family_name' => 'Lovelace']);

    $gedcom = app(ExportGenealogyData::class)->execute('gedcom', 'GEDCOM archive');
    $gramps = app(ExportGenealogyData::class)->execute('gramps-xml', 'GRAMPS archive');

    expect($gedcom->transfer->status)->toBe('completed')
        ->and($gedcom->transfer->direction)->toBe('export')
        ->and($gedcom->transfer->records_count)->toBe(1)
        ->and($gedcom->transfer->metadata['sha256'])->toBe(hash('sha256', $gedcom->content))
        ->and($gedcom->content)->toContain('Ada /Lovelace/')
        ->and($gramps->filename)->toBe('genealogy.gramps.xml')
        ->and($gramps->content)->toContain('<database')
        ->and((string) $person->fresh()->team_id)->toBe((string) $team->id);
});

it('exposes audited export downloads through API and Livewire adapters', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->forceFill(['current_team_id' => $team->getKey()])->save();
    app(TeamContext::class)->set($team->id);
    (new CreatePerson())->execute(['given_name' => 'Grace', 'family_name' => 'Hopper']);
    app(TeamContext::class)->clear();

    $this->actingAs($user)
        ->get('/api/v1/genealogy/import-export/export?format=gedcom&name=API%20archive')
        ->assertOk()
        ->assertHeader('Content-Disposition')
        ->assertHeader('X-Data-Transfer-Status', 'completed');

    app(TeamContext::class)->set($team->id);
    Livewire::actingAs($user)
        ->test('genealogy-import-export-export')
        ->set('format', 'gramps-xml')
        ->set('name', 'Livewire archive')
        ->call('export')
        ->assertDispatched('genealogy-export-completed');

    expect(DataTransfer::query()->where('direction', 'export')->count())->toBe(2);
});
