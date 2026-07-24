<?php

declare(strict_types=1);

namespace Tests\Filament;

use App\Filament\App\Pages\ReferAndEarn;
use App\Models\Referral;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReferAndEarnPageTest extends TestCase
{
    use RefreshDatabase;

    private function actingUser(): User
    {
        config(['affiliate.enabled' => true, 'premium.enabled' => false, 'affiliate.referrals_per_free_month' => 5]);
        $user = User::factory()->withPersonalTeam()->create();
        $this->actingAs($user);
        Filament::setTenant($user->currentTeam, isQuiet: true);

        return $user;
    }

    public function test_gated_off_when_dormant(): void
    {
        $this->actingUser();
        config(['premium.enabled' => true]); // everyone premium => dormant

        $this->assertFalse(ReferAndEarn::canAccess());
        $this->assertFalse(ReferAndEarn::shouldRegisterNavigation());
    }

    public function test_visible_when_program_live(): void
    {
        $this->actingUser();

        $this->assertTrue(ReferAndEarn::canAccess());
        $this->assertTrue(ReferAndEarn::shouldRegisterNavigation());
    }

    public function test_renders_link_and_progress(): void
    {
        $user = $this->actingUser();

        Livewire::test(ReferAndEarn::class)
            ->assertOk()
            ->assertSee($user->referralCode())
            ->assertSee('0 / 5');
    }

    public function test_renders_referrals_with_status_and_progress(): void
    {
        $user = $this->actingUser();

        $qualified = User::factory()->create(['name' => 'Paid Pat']);
        Referral::create([
            'referrer_id' => $user->id, 'referred_user_id' => $qualified->id,
            'status' => Referral::STATUS_QUALIFIED, 'qualified_at' => now(),
        ]);
        $pending = User::factory()->create(['name' => 'Maybe Max']);
        Referral::create([
            'referrer_id' => $user->id, 'referred_user_id' => $pending->id,
            'status' => Referral::STATUS_PENDING,
        ]);

        Livewire::test(ReferAndEarn::class)
            ->assertOk()
            ->assertSee('Paid Pat')
            ->assertSee('Maybe Max')
            ->assertSee('Qualified')
            ->assertSee('Pending')
            ->assertSee('1 / 5'); // one unconsumed qualified toward 5
    }
}
