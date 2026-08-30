<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('connected_accounts', function (Blueprint $table): void {
            $table->boolean('enable_family_matching')->default(false);
            $table->json('cached_profile_data')->nullable();
            $table->timestamp('last_synced_at')->nullable();
        });

        Schema::create('genealogy_social_connection_privacy', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('allow_family_discovery')->default(true);
            $table->boolean('show_profile_to_matches')->default(true);
            $table->boolean('share_tree_with_matches')->default(false);
            $table->boolean('allow_contact_from_matches')->default(true);
            $table->json('blocked_users')->nullable();
            $table->timestamps();
            $table->unique('user_id');
        });

        Schema::create('genealogy_social_family_connections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('connected_account_id')->constrained('connected_accounts')->cascadeOnDelete();
            $table->string('matched_social_id');
            $table->string('matched_name')->nullable();
            $table->string('matched_email')->nullable();
            $table->string('relationship_type')->nullable();
            $table->unsignedTinyInteger('confidence_score')->default(0);
            $table->json('matching_criteria')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['connected_account_id', 'matched_social_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genealogy_social_family_connections');
        Schema::dropIfExists('genealogy_social_connection_privacy');
        Schema::table('connected_accounts', function (Blueprint $table): void {
            $table->dropColumn(['enable_family_matching', 'cached_profile_data', 'last_synced_at']);
        });
    }
};
