<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Api;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Liberu\Foundation\ApiAccess\Http\Middleware\ApiContract;
use Liberu\Genealogy\GenealogyCore\Http\Middleware\EstablishTeamContext;

final class CollaborationApiServiceProvider extends ServiceProvider
{
    public function boot(Router $router): void
    {
        $router->post('api/v1/genealogy/collaboration/invitations/{record}/accept', [CollaborationWorkflowController::class, 'accept'])
            ->middleware(['api', 'auth:sanctum', ApiContract::class, 'throttle:60,1']);
        $router->middleware(['api', 'auth:sanctum', EstablishTeamContext::class, ApiContract::class, 'throttle:60,1'])->group(function () use ($router): void {
            $router->apiResource('api/v1/genealogy/collaboration/proposals', CollaborationProposalController::class)
                ->only(['index', 'store', 'show', 'update', 'destroy'])
                ->parameters(['proposals' => 'record']);
            $router->post('api/v1/genealogy/collaboration/proposals/{record}/review', [CollaborationProposalController::class, 'review'])
                ->name('genealogy.collaboration.proposals.review');
            $router->get('api/v1/genealogy/collaboration/invitations', [CollaborationWorkflowController::class, 'invitations']);
            $router->post('api/v1/genealogy/collaboration/invitations', [CollaborationWorkflowController::class, 'invite']);
            $router->post('api/v1/genealogy/collaboration/invitations/{record}/revoke', [CollaborationWorkflowController::class, 'revoke']);
            $router->get('api/v1/genealogy/collaboration/memberships', [CollaborationWorkflowController::class, 'memberships']);
            $router->patch('api/v1/genealogy/collaboration/memberships/{record}', [CollaborationWorkflowController::class, 'updateMembership']);
            $router->get('api/v1/genealogy/collaboration/discussions', [CollaborationWorkflowController::class, 'discussions']);
            $router->post('api/v1/genealogy/collaboration/discussions', [CollaborationWorkflowController::class, 'createDiscussion']);
            $router->patch('api/v1/genealogy/collaboration/discussions/{record}', [CollaborationWorkflowController::class, 'updateDiscussion']);
            $router->delete('api/v1/genealogy/collaboration/discussions/{record}', [CollaborationWorkflowController::class, 'deleteDiscussion']);
            $router->get('api/v1/genealogy/collaboration/watches', [CollaborationWorkflowController::class, 'watches']);
            $router->post('api/v1/genealogy/collaboration/watches/toggle', [CollaborationWorkflowController::class, 'toggleWatch']);
            $router->get('api/v1/genealogy/collaboration/attributions', [CollaborationWorkflowController::class, 'attributions']);
            $router->apiResource('api/v1/genealogy/collaboration', CollaborationSpaceController::class)
                ->parameters(['collaboration-spaces' => 'record']);
        });
    }
}
