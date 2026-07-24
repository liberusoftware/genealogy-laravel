<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One granted free month (the ledger row ticket 03 writes). `referrals_consumed`
 * summed across a user's rewards is the "already spent" side of the free-months
 * owed calculation. Not team-scoped.
 */
class AffiliateReward extends Model
{
    use HasFactory;

    public const DELIVERY_STRIPE_CREDIT = 'stripe_credit';

    public const DELIVERY_ACCESS_EXTENSION = 'access_extension';

    protected $fillable = [
        'user_id',
        'referrals_consumed',
        'delivery',
        'amount_cents',
        'granted_at',
    ];

    protected function casts(): array
    {
        return [
            'referrals_consumed' => 'integer',
            'amount_cents' => 'integer',
            'granted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
