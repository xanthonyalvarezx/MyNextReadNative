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

        if (! Schema::hasColumn('libraries', 'google_volume_id')) {
            Schema::table('libraries', function (Blueprint $table) {
                $table->string('google_volume_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('libraries')) {
            return;
        }

        if (Schema::hasColumn('libraries', 'google_volume_id')) {
            Schema::table('libraries', function (Blueprint $table) {
                $table->dropColumn('google_volume_id');
            });
        }
    }
};
