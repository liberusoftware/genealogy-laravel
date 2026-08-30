<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Discovery\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Liberu\Foundation\Identity\Socialstream\Models\ConnectedAccount;

final class SocialFamilyConnection extends Model
{
    protected $table = 'genealogy_social_family_connections';

    protected $fillable = ['user_id', 'connected_account_id', 'matched_social_id', 'matched_name', 'matched_email', 'relationship_type', 'confidence_score', 'matching_criteria', 'status'];

    protected function casts(): array
    {
        return ['confidence_score' => 'integer', 'matching_criteria' => 'array'];
    }

    public function connectedAccount(): BelongsTo
    {
        return $this->belongsTo(ConnectedAccount::class);
    }

    public function accept(): void
    {
        $this->update(['status' => 'accepted']);
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
