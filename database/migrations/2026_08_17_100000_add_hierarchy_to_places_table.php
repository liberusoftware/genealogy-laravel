<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table) {
            // Adds the nullable parent_id column for self-referencing hierarchy.
            // It is constrained to the same table, creating the relationship.
            // The `after('id')` is for readability in the database schema.
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('places')->onDelete('set null');

            // Adds nullable decimal columns for storing coordinates.
            // The precision and scale are standard for GPS coordinates.
            $table->decimal('latitude', 10, 8)->nullable()->after('date');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            // To safely roll back, we must drop the foreign key constraint
            // before dropping the column itself.
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'latitude', 'longitude']);
        });
    }
};
