<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The `settings` table is hand-rolled in 2023_05_15_000000_create_site_settings_table
 * and never got spatie's unique index on (group, name). spatie/laravel-settings
 * saves with an upsert keyed on exactly those two columns, so without it:
 *
 *   - SQLite throws "ON CONFLICT clause does not match any PRIMARY KEY or UNIQUE
 *     constraint" — every settings save fails outright (this is what the test
 *     suite hits);
 *   - MySQL/MariaDB has no key to conflict on, so the upsert INSERTs instead of
 *     UPDATEs and the table quietly accumulates duplicate rows, with the reader
 *     picking whichever comes first.
 *
 * Deduplicate before indexing, keeping the highest id per pair — that is the row
 * a duplicating upsert wrote last, i.e. the most recent save.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $keep = DB::table('settings')
            ->selectRaw('MAX(id) as id')
            ->groupBy('group', 'name')
            ->pluck('id');

        DB::table('settings')->whereNotIn('id', $keep)->delete();

        Schema::table('settings', function (Blueprint $table): void {
            $table->unique(['group', 'name']);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('settings')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->dropUnique(['group', 'name']);
            });
        }
    }
};
