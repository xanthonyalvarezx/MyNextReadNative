<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fixes installs where 2026_03_29_180000_create_libraries_table accidentally created `librar`
     * instead of `libraries`, so every later migration no-op'd and the app queried a missing table.
     */
    public function up(): void
    {
        if (Schema::hasTable('librar') && ! Schema::hasTable('libraries')) {
            Schema::rename('librar', 'libraries');
        }

        if (! Schema::hasTable('libraries')) {
            return;
        }

        Schema::table('libraries', function (Blueprint $table) {
            if (! Schema::hasColumn('libraries', 'description')) {
                $table->text('description')->nullable();
            }
            if (! Schema::hasColumn('libraries', 'subtitle')) {
                $table->text('subtitle')->nullable();
            }
            if (! Schema::hasColumn('libraries', 'language')) {
                $table->string('language', 32)->nullable();
            }
            if (! Schema::hasColumn('libraries', 'genre')) {
                $table->text('genre')->nullable();
            }
            if (! Schema::hasColumn('libraries', 'pages_read')) {
                $table->unsignedInteger('pages_read')->default(0);
            }
        });
    }

    public function down(): void
    {
        //
    }
};
