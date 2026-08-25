<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Dna\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\ApiAccess\Http\Middleware\ApiContract;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class DnaApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class, ApiContract::class, 'throttle:60,1'])->group(function () use ($router): void {
            $router->apiResource('api/v1/genealogy/dna/providers', DnaProviderController::class)->parameters(['providers' => 'record']);
            $router->post('api/v1/genealogy/dna/kits/import', [DnaKitController::class, 'import'])->name('genealogy.dna.kits.import');
            $router->post('api/v1/genealogy/dna/kits/{kit}/consent', [DnaKitController::class, 'consent'])->name('genealogy.dna.consent');
            $router->get('api/v1/genealogy/dna/kits/{kit}/consents', [DnaKitController::class, 'consents'])->name('genealogy.dna.consents');
            $router->post('api/v1/genealogy/dna/kits/{kit}/revoke', [DnaKitController::class, 'revoke'])->name('genealogy.dna.revoke');
            $router->post('api/v1/genealogy/dna/kits/validate', [DnaKitController::class, 'validateFile'])->name('genealogy.dna.kits.validate');
            $router->apiResource('api/v1/genealogy/dna/kits', DnaKitController::class)->parameters(['kits' => 'record']);
            $router->post('api/v1/genealogy/dna/matches/analyze', [DnaMatchController::class, 'analyze'])->name('genealogy.dna.matches.analyze');
            $router->post('api/v1/genealogy/dna/matches/compare', [DnaMatchController::class, 'compare'])->name('genealogy.dna.matches.compare');
            $router->post('api/v1/genealogy/dna/matches/compare-and-persist', [DnaMatchController::class, 'compareAndPersist'])->name('genealogy.dna.matches.compare-and-persist');
            $router->post('api/v1/genealogy/dna/matches/triangulate', [DnaMatchController::class, 'triangulate'])->name('genealogy.dna.matches.triangulate');
            $router->apiResource('api/v1/genealogy/dna/matches', DnaMatchController::class)->parameters(['matches' => 'record']);
            $router->post('api/v1/genealogy/dna/matches/{record}/segments', [DnaMatchController::class, 'segment'])->name('genealogy.dna.segment');
            $router->get('api/v1/genealogy/dna/matches/{record}/segments', [DnaMatchController::class, 'segments'])->name('genealogy.dna.segments');
            $router->patch('api/v1/genealogy/dna/segments/{record}', [DnaMatchController::class, 'updateSegment'])->name('genealogy.dna.segments.update');
            $router->delete('api/v1/genealogy/dna/segments/{record}', [DnaMatchController::class, 'deleteSegment'])->name('genealogy.dna.segments.delete');
            $router->apiResource('api/v1/genealogy/dna/groups', DnaGroupController::class)->parameters(['groups' => 'record']);
            $router->get('api/v1/genealogy/dna/notes', [DnaAnnotationController::class, 'notes'])->name('genealogy.dna.notes.index');
            $router->post('api/v1/genealogy/dna/notes', [DnaAnnotationController::class, 'createNote'])->name('genealogy.dna.notes.store');
            $router->delete('api/v1/genealogy/dna/notes/{record}', [DnaAnnotationController::class, 'deleteNote'])->name('genealogy.dna.notes.delete');
            $router->get('api/v1/genealogy/dna/relationships', [DnaAnnotationController::class, 'relationships'])->name('genealogy.dna.relationships.index');
            $router->post('api/v1/genealogy/dna/relationships', [DnaAnnotationController::class, 'createRelationship'])->name('genealogy.dna.relationships.store');
            $router->delete('api/v1/genealogy/dna/relationships/{record}', [DnaAnnotationController::class, 'deleteRelationship'])->name('genealogy.dna.relationships.delete');
        });
    }
}
