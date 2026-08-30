<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Liberu\Genealogy\GenealogyCore\Concerns\BelongsToTeam;

final class VirtualEvent extends Model
{
    use BelongsToTeam;
    use HasUuids;
    use SoftDeletes;

    protected $table = 'genealogy_virtual_events';

    protected $fillable = ['team_id', 'created_by', 'title', 'description', 'start_time', 'end_time', 'timezone', 'status', 'platform', 'meeting_id', 'meeting_password', 'meeting_url', 'join_url', 'platform_data', 'max_attendees', 'require_rsvp', 'allow_guests', 'instructions', 'host_email'];

    protected $attributes = ['status' => 'draft', 'platform' => 'zoom', 'timezone' => 'UTC', 'require_rsvp' => true, 'allow_guests' => false];

    protected function casts(): array
    {
        return ['start_time' => 'datetime', 'end_time' => 'datetime', 'platform_data' => 'array', 'max_attendees' => 'integer', 'require_rsvp' => 'boolean', 'allow_guests' => 'boolean'];
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(VirtualEventAttendee::class, 'virtual_event_id');
    }

    public function acceptedAttendees(): HasMany
    {
        return $this->attendees()->where('rsvp_status', 'accepted');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_time', '>', now());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('start_time', '<=', now())->where('end_time', '>=', now());
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function canJoin(): bool
    {
        return $this->status === 'published' && $this->start_time <= now()->addMinutes(15) && $this->end_time >= now();
    }

    public function isAtCapacity(): bool
    {
        return $this->max_attendees !== null && $this->acceptedAttendees()->count() >= $this->max_attendees;
    }
}
