<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\Evidence\Actions\ArchiveEvidenceRecord;
use Liberu\Genealogy\Evidence\Actions\CreateAssertion;
use Liberu\Genealogy\Evidence\Actions\CreateCitation;
use Liberu\Genealogy\Evidence\Actions\CreateEvidenceRecord;
use Liberu\Genealogy\Evidence\Actions\CreateExtract;
use Liberu\Genealogy\Evidence\Actions\CreateProofConclusion;
use Liberu\Genealogy\Evidence\Actions\CreateRepository;
use Liberu\Genealogy\Evidence\Actions\CreateSource;
use Liberu\Genealogy\Evidence\Actions\ReviewEvidenceRecord;
use Liberu\Genealogy\Evidence\Events\EvidenceRecordArchived;
use Liberu\Genealogy\Evidence\Events\EvidenceRecordCreated;
use Liberu\Genealogy\Evidence\Events\EvidenceRecordReviewed;
use Liberu\Genealogy\Evidence\Filament\Resources\AssertionResource;
use Liberu\Genealogy\Evidence\Filament\Resources\CitationResource;
use Liberu\Genealogy\Evidence\Filament\Resources\ExtractResource;
use Liberu\Genealogy\Evidence\Filament\Resources\ProofConclusionResource;
use Liberu\Genealogy\Evidence\Filament\Resources\RepositoryResource;
use Liberu\Genealogy\Evidence\Filament\Resources\SourceResource;
use Liberu\Genealogy\Evidence\Livewire\EvidenceRecordList;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Actions\CreatePerson;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('records tenant-owned evidence with confidence and proof semantics', function (): void {
    Event::fake();
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $person = (new CreatePerson())->execute(['given_name' => 'Ada']);

    $record = (new CreateEvidenceRecord())->execute([
        'name' => 'Parish register entry',
        'kind' => 'proof_conclusion',
        'assertion' => 'Ada was born in London.',
        'proof_conclusion' => 'The register supports the birth assertion.',
        'confidence' => 90,
        'subject_person_id' => $person->id,
        'status' => 'completed',
    ]);

    expect($record->team_id)->toBe((string) $team->id)
        ->and($record->isHighConfidence())->toBeTrue()
        ->and($record->hasProofConclusion())->toBeTrue();
    Event::assertDispatched(EvidenceRecordCreated::class);
});

it('rejects proof conclusions without assertions and cross-team subjects', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $person = (new CreatePerson())->execute(['given_name' => 'Local']);

    expect(fn () => (new CreateEvidenceRecord())->execute([
        'name' => 'Incomplete proof',
        'proof_conclusion' => 'Unsupported conclusion',
    ]))->toThrow(InvalidArgumentException::class, 'assertion');

    $otherUser = User::factory()->create();
    $otherTeam = Team::factory()->create(['user_id' => $otherUser->id]);
    app(TeamContext::class)->set($otherTeam->id);
    $remote = (new CreatePerson())->execute(['given_name' => 'Remote']);
    app(TeamContext::class)->set($team->id);

    expect(fn () => (new CreateEvidenceRecord())->execute([
        'name' => 'Cross-team source',
        'subject_person_id' => $remote->id,
    ]))->toThrow(InvalidArgumentException::class, 'active team');
});

it('uses the stable evidence resource type in the API', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    $this->actingAs($user)
        ->postJson('/api/v1/genealogy/evidence', ['name' => 'Census', 'kind' => 'source'])
        ->assertCreated()
        ->assertJsonPath('data.type', 'genealogy-evidence');
});

it('reviews and archives evidence through explicit lifecycle actions', function (): void {
    Event::fake();
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $record = (new CreateEvidenceRecord())->execute([
        'name' => 'Reviewed source',
        'assertion' => 'The source supports the claim.',
        'status' => 'active',
    ]);

    (new ReviewEvidenceRecord())->execute($record);
    expect($record->fresh()->status)->toBe('completed')
        ->and($record->fresh()->reviewed_at)->not->toBeNull();
    Event::assertDispatched(EvidenceRecordReviewed::class);

    (new ArchiveEvidenceRecord())->execute($record->fresh());
    expect($record->fresh()->status)->toBe('archived');
    Event::assertDispatched(EvidenceRecordArchived::class);
});

it('exposes lifecycle actions through the authenticated evidence API', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->forceFill(['current_team_id' => $team->getKey()])->save();
    app(TeamContext::class)->set($team->id);
    $record = (new CreateEvidenceRecord())->execute([
        'name' => 'API lifecycle source',
        'assertion' => 'The source supports the claim.',
        'status' => 'active',
    ]);
    app(TeamContext::class)->clear();

    $this->actingAs($user)
        ->postJson('/api/v1/genealogy/evidence/'.$record->getKey().'/review')
        ->assertOk()
        ->assertJsonPath('data.attributes.status', 'completed');

    $this->actingAs($user)
        ->postJson('/api/v1/genealogy/evidence/'.$record->getKey().'/archive')
        ->assertOk()
        ->assertJsonPath('data.attributes.status', 'archived');
});

it('rejects reviewing an archived evidence record', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $record = (new CreateEvidenceRecord())->execute(['name' => 'Archived source']);
    (new ArchiveEvidenceRecord())->execute($record);

    expect(fn () => (new ReviewEvidenceRecord())->execute($record->fresh()))
        ->toThrow(InvalidArgumentException::class, 'archived');
});

it('rejects direct lifecycle mutations for records outside the active team', function (): void {
    $firstTeam = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($firstTeam->id);
    $record = (new CreateEvidenceRecord())->execute(['name' => 'Private source', 'status' => 'active']);

    $secondTeam = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($secondTeam->id);

    expect(fn () => (new ReviewEvidenceRecord())->execute($record->withoutRelations()))
        ->toThrow(InvalidArgumentException::class, 'active team');
    expect(fn () => (new ArchiveEvidenceRecord())->execute($record->withoutRelations()))
        ->toThrow(InvalidArgumentException::class, 'active team');
});

it('lets authenticated Livewire users review and archive tenant evidence', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);
    $record = (new CreateEvidenceRecord())->execute([
        'name' => 'Interactive evidence',
        'assertion' => 'The source supports the claim.',
        'status' => 'active',
    ]);

    Livewire::actingAs($user)
        ->test(EvidenceRecordList::class)
        ->call('review', (string) $record->getKey())
        ->assertDispatched('evidence-record-reviewed')
        ->call('archive', (string) $record->getKey())
        ->assertDispatched('evidence-record-archived');

    expect($record->fresh()->status)->toBe('archived');
});

it('supports the complete evidence chain through tenant-scoped domain actions', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    $source = app(CreateSource::class)->execute(['name' => 'Parish register']);
    $repository = app(CreateRepository::class)->execute(['name' => 'County archive', 'source_id' => $source->id]);
    $citation = app(CreateCitation::class)->execute([
        'source_id' => $source->id,
        'repository_id' => $repository->id,
        'page' => '42',
        'confidence' => 85,
    ]);
    $extract = app(CreateExtract::class)->execute(['citation_id' => $citation->id, 'content' => 'A faithful extract.']);
    $assertion = app(CreateAssertion::class)->execute([
        'citation_id' => $citation->id,
        'extract_id' => $extract->id,
        'statement' => 'The record supports the event.',
        'confidence' => 90,
    ]);
    $conclusion = app(CreateProofConclusion::class)->execute([
        'assertion_id' => $assertion->id,
        'conclusion' => 'The evidence is sufficient for the conclusion.',
        'confidence' => 90,
    ]);

    expect($conclusion->assertion->extract->citation->source->repositories->first()->id)
        ->toBe($repository->id)
        ->and($conclusion->team_id)->toBe((string) $team->id);
});

it('rejects supporting evidence references from another team', function (): void {
    $firstTeam = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($firstTeam->id);
    $source = app(CreateSource::class)->execute(['name' => 'Private archive']);

    $secondTeam = Team::factory()->create(['user_id' => User::factory()->create()->id]);
    app(TeamContext::class)->set($secondTeam->id);

    expect(fn () => app(CreateRepository::class)->execute([
        'name' => 'Cross-team repository',
        'source_id' => $source->id,
    ]))->toThrow(InvalidArgumentException::class, 'active team');
});

it('exposes evidence subdomains through explicit API resources', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->forceFill(['current_team_id' => $team->getKey()])->save();
    app(TeamContext::class)->set($team->id);

    $this->actingAs($user)
        ->postJson('/api/v1/genealogy/evidence/sources', ['name' => 'Census source'])
        ->assertCreated()
        ->assertJsonPath('data.type', 'genealogy-evidence-genealogy_evidence_sources');
});

it('bounds evidence entity pagination through the API contract', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    $user->forceFill(['current_team_id' => $team->getKey()])->save();
    app(TeamContext::class)->set($team->id);

    $this->actingAs($user)
        ->getJson('/api/v1/genealogy/evidence/sources?page%5Bsize%5D=101')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['page.size']);
});

it('registers full Filament page workflows for every evidence entity resource', function (): void {
    foreach ([SourceResource::class, RepositoryResource::class, CitationResource::class, ExtractResource::class, AssertionResource::class, ProofConclusionResource::class] as $resource) {
        expect($resource::getPages())->toHaveKeys(['index', 'create', 'edit']);
    }
});
