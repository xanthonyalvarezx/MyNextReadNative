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

        if (! Schema::hasColumn('libraries', 'description')) {
            Schema::table('libraries', function (Blueprint $table) {
                $table->text('description')->nullable()->after('cover_image');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('libraries')) {
            return;
        }

        if (Schema::hasColumn('libraries', 'description')) {
            Schema::table('libraries', function (Blueprint $table) {
                $table->dropColumn('description');
            });
        }
    }
};
