<?php

declare(strict_types=1);

namespace Liberu\Genealogy\Collaboration\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

final class VirtualEventAttendee extends Model
{
    use HasUuids;

    protected $table = 'genealogy_virtual_event_attendees';

    protected $fillable = ['virtual_event_id', 'user_id', 'person_id', 'guest_name', 'guest_email', 'rsvp_status', 'rsvp_date', 'rsvp_notes', 'attended', 'joined_at', 'left_at', 'duration_minutes', 'attendance_data', 'is_host', 'is_moderator', 'invitation_token', 'invitation_sent_at'];

    protected $attributes = ['rsvp_status' => 'pending', 'attended' => false, 'is_host' => false, 'is_moderator' => false];

    protected function casts(): array
    {
        return ['rsvp_date' => 'datetime', 'joined_at' => 'datetime', 'left_at' => 'datetime', 'attendance_data' => 'array', 'attended' => 'boolean', 'is_host' => 'boolean', 'is_moderator' => 'boolean', 'invitation_sent_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        self::creating(function (self $attendee): void {
            $attendee->invitation_token ??= Str::random(32);
        });
    }

    public function virtualEvent(): BelongsTo
    {
        return $this->belongsTo(VirtualEvent::class, 'virtual_event_id');
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('rsvp_status', 'accepted');
    }

    public function scopeAttended(Builder $query): Builder
    {
        return $query->where('attended', true);
    }

    public function accept(?string $notes = null): void
    {
        $this->update(['rsvp_status' => 'accepted', 'rsvp_date' => now(), 'rsvp_notes' => $notes]);
    }

    public function decline(?string $notes = null): void
    {
        $this->update(['rsvp_status' => 'declined', 'rsvp_date' => now(), 'rsvp_notes' => $notes]);
    }

    public function maybe(?string $notes = null): void
    {
        $this->update(['rsvp_status' => 'maybe', 'rsvp_date' => now(), 'rsvp_notes' => $notes]);
    }

    public function markAsAttended(array $attendanceData = []): void
    {
        $this->update(['attended' => true, 'joined_at' => $attendanceData['joined_at'] ?? now(), 'left_at' => $attendanceData['left_at'] ?? null, 'duration_minutes' => $attendanceData['duration_minutes'] ?? null, 'attendance_data' => $attendanceData]);
    }
}
