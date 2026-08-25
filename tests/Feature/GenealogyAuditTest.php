<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Liberu\Foundation\Organizations\Models\Team;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Liberu\Genealogy\People\Actions\CreatePerson;
use Liberu\Genealogy\People\Models\Person;

uses(RefreshDatabase::class);

it('records genealogy model lifecycle changes with tenant context', function (): void {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);
    app(TeamContext::class)->set($team->id);

    $person = app(CreatePerson::class)->execute(['given_name' => 'Ada']);
    $person->update(['family_name' => 'Lovelace']);
    $person->delete();

    $events = DB::table('activity_log')
        ->where('subject_type', Person::class)
        ->orderBy('id')
        ->get();

    expect($events)->toHaveCount(3)
        ->and($events->pluck('event')->all())->toBe(['genealogy_created', 'genealogy_updated', 'genealogy_deleted'])
        ->and($events->pluck('tenant_ref')->unique()->all())->toBe([(string) $team->id])
        ->and($events->pluck('record_hash')->filter()->count())->toBe(3);
});
