<?php

declare(strict_types=1);

namespace Tests\Filament\Widgets;

use App\Filament\App\Widgets\ReferralProgressWidget;
use App\Models\Referral;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class ReferralProgressWidgetTest extends TestCase
{
    use RefreshDatabase;

    private function actingUser(): User
    {
        config(['affiliate.enabled' => true, 'premium.enabled' => false, 'affiliate.referrals_per_free_month' => 5]);
        Filament::setCurrentPanel(Filament::getPanel('app'));
        $user = User::factory()->withPersonalTeam()->create();
        $this->actingAs($user);
        Filament::setTenant($user->currentTeam, isQuiet: true);

        return $user;
    }

    /** @return array<string, Stat> label => stat */
    private function stats(): array
    {
        $method = new ReflectionMethod(ReferralProgressWidget::class, 'getStats');
        $method->setAccessible(true);

        return collect($method->invoke(new ReferralProgressWidget))
            ->keyBy(fn (Stat $s): string => $s->getLabel())
            ->all();
    }

    public function test_hidden_when_dormant(): void
    {
        $this->actingUser();
        config(['premium.enabled' => true]); // everyone premium => dormant

        $this->assertFalse(ReferralProgressWidget::canView());
    }

    public function test_visible_when_live(): void
    {
        $this->actingUser();

        $this->assertTrue(ReferralProgressWidget::canView());
    }

    public function test_stats_reflect_progress(): void
    {
        $user = $this->actingUser();

        $stats = $this->stats();
        $this->assertSame('0 / 5', $stats['Next free month']->getValue());
        $this->assertSame(0, $stats['Free months earned']->getValue());

        $referred = User::factory()->create();
        Referral::create([
            'referrer_id' => $user->id, 'referred_user_id' => $referred->id,
            'status' => Referral::STATUS_QUALIFIED, 'qualified_at' => now(),
        ]);

        $this->assertSame('1 / 5', $this->stats()['Next free month']->getValue());
    }
}
