<?php

declare(strict_types=1);

namespace Liberu\Genealogy\GenealogyCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Liberu\Genealogy\GenealogyCore\TeamContext;
use Symfony\Component\HttpFoundation\Response;

final class EstablishTeamContext
{
    public function handle(Request $request, Closure $next): Response
    {
        // This binding is a singleton and can outlive one request under
        // Octane, queue workers, or another long-lived runtime. A guest
        // request must never inherit the previous request's tenant.
        $context = app(TeamContext::class);
        $context->clear();

        $actor = $request->user();

        if ($actor === null) {
            return $next($request);
        }

        $team = $actor->currentTeam;
        abort_unless($team !== null && ($team->status ?? 'active') === 'active', 403, 'An active team context is required.');

        $member = method_exists($actor, 'belongsToTeam') && $actor->belongsToTeam($team);
        abort_unless($member, 403, 'You are not a member of the selected team.');

        $context->set($team->getKey());

        try {
            return $next($request);
        } finally {
            $context->clear();
        }
    }
}
