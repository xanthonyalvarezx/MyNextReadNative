<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('libraries')) {
            return;
        }

        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        // SQLite: drop the index with raw SQL first; Laravel's dropIndex + dropColumn can leave the
        // table in a state where DROP COLUMN fails ("error in index ... after drop column").
        if ($driver === 'sqlite') {
            $connection->statement('DROP INDEX IF EXISTS libraries_google_volume_id_index');
        }

        if (! Schema::hasColumn('libraries', 'google_volume_id')) {
            return;
        }

        if ($driver !== 'sqlite') {
            Schema::table('libraries', function (Blueprint $table) {
                $table->dropIndex(['google_volume_id']);
            });
        }

        Schema::table('libraries', function (Blueprint $table) {
            $table->dropColumn('google_volume_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('libraries')) {
            return;
        }

        if (! Schema::hasColumn('libraries', 'google_volume_id')) {
            Schema::table('libraries', function (Blueprint $table) {
                $table->string('google_volume_id')->nullable();
            });
            Schema::table('libraries', function (Blueprint $table) {
                $table->index('google_volume_id');
            });
        }
    }
};
