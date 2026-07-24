<?php

namespace Tests\Feature\Filament;

use App\Filament\App\Pages\DnaTriangulationPage;
use App\Filament\App\Pages\TeamMembers;
use App\Filament\App\Resources\DnaMatchingResource;
use App\Filament\App\Resources\DnaResource;
use App\Filament\App\Resources\DuplicateCheckResource;
use App\Filament\App\Resources\SmartMatchResource;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * #1630: a non-premium user who reaches a gated premium surface by direct URL
 * is redirected to sign-up, not shown a bare 403. The gate throws
 * PremiumRequiredException; the handler in bootstrap/app.php converts it to a
 * redirect. Revert-sensitive: before #1630 the gate returned false → 403, so the
 * assertRedirect assertions fail on the old code.
 */
class PremiumGateRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsUser(array $attributes): User
    {
        $user = User::factory()->withPersonalTeam()->create($attributes);
        $this->actingAs($user);
        Filament::setTenant($user->currentTeam, isQuiet: true);

        return $user;
    }

    /** @return array<string,string> Every gated premium surface's URL. */
    private function premiumUrls(): array
    {
        return [
            'dna' => DnaResource::getUrl('index'),
            'dna-matching' => DnaMatchingResource::getUrl('index'),
            'smart-match' => SmartMatchResource::getUrl('index'),
            'duplicate-check' => DuplicateCheckResource::getUrl('index'),
            'triangulation' => DnaTriangulationPage::getUrl(),
        ];
    }

    public function test_non_premium_user_is_redirected_to_subscription_on_every_gated_surface(): void
    {
        config(['premium.enabled' => false]);
        $user = $this->actingAsUser(['is_premium' => false, 'trial_ends_at' => null]);
        $target = route('filament.app.pages.subscription', ['tenant' => $user->currentTeam]);

        foreach ($this->premiumUrls() as $label => $url) {
            $this->get($url)->assertRedirect($target);
        }
    }

    public function test_expired_trial_user_is_redirected_to_trial_expired(): void
    {
        config(['premium.enabled' => false]);
        $user = $this->actingAsUser(['is_premium' => true, 'trial_ends_at' => now()->subDay()]);

        $this->get(DnaResource::getUrl('index'))->assertRedirect(
            route('filament.app.pages.trial-expired', ['tenant' => $user->currentTeam]),
        );
    }

    public function test_a_tenant_403_is_not_hijacked_into_a_signup_redirect(): void
    {
        config(['premium.enabled' => false]);

        $owner = User::factory()->withPersonalTeam()->create();
        $team = $owner->currentTeam;

        // A non-owner member who IS a subscriber — so the #1635 paywall gate
        // lets them into the panel. TeamMembers is owner-only, so it 403s for a
        // tenant reason. The handler keys on the exception type, not on "any 403",
        // so this stays a 403 and is not turned into a subscription redirect.
        // (Under the paywall a *non*-subscriber never reaches here — they're
        // bounced to subscribe first; see PaywallGateTest.)
        $member = User::factory()->create(['current_team_id' => $team->id, 'stripe_id' => 'cus_member']);
        $member->subscriptions()->create([
            'type' => 'premium',
            'stripe_id' => 'sub_member',
            'stripe_status' => 'active',
            'stripe_price' => 'price_premium_monthly',
            'quantity' => 1,
        ]);
        $team->users()->attach($member, ['role' => 'viewer']);

        $this->actingAs($member->fresh());
        Filament::setTenant($team->fresh(), isQuiet: true);

        $this->get(TeamMembers::getUrl(['tenant' => $team]))->assertStatus(403);
    }
}
