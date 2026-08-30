<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('genealogy_virtual_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('timezone')->default('UTC');
            $table->string('status')->default('draft');
            $table->string('platform')->default('zoom');
            $table->string('meeting_id')->nullable();
            $table->string('meeting_password')->nullable();
            $table->text('meeting_url')->nullable();
            $table->text('join_url')->nullable();
            $table->json('platform_data')->nullable();
            $table->unsignedInteger('max_attendees')->nullable();
            $table->boolean('require_rsvp')->default(true);
            $table->boolean('allow_guests')->default(false);
            $table->text('instructions')->nullable();
            $table->string('host_email')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['team_id', 'status']);
            $table->index(['start_time', 'end_time']);
        });

        Schema::create('genealogy_virtual_event_attendees', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('virtual_event_id')->constrained('genealogy_virtual_events')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->uuid('person_id')->nullable();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('rsvp_status')->default('pending');
            $table->dateTime('rsvp_date')->nullable();
            $table->text('rsvp_notes')->nullable();
            $table->boolean('attended')->default(false);
            $table->dateTime('joined_at')->nullable();
            $table->dateTime('left_at')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->json('attendance_data')->nullable();
            $table->boolean('is_host')->default(false);
            $table->boolean('is_moderator')->default(false);
            $table->string('invitation_token')->nullable();
            $table->dateTime('invitation_sent_at')->nullable();
            $table->timestamps();
            $table->unique(['virtual_event_id', 'user_id']);
            $table->unique(['virtual_event_id', 'person_id']);
            $table->index('rsvp_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genealogy_virtual_event_attendees');
        Schema::dropIfExists('genealogy_virtual_events');
    }
};
