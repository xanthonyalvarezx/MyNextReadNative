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

        Schema::table('libraries', function (Blueprint $table) {
            if (! Schema::hasColumn('libraries', 'subtitle')) {
                $table->text('subtitle')->nullable();
            }
            if (! Schema::hasColumn('libraries', 'language')) {
                $table->string('language', 32)->nullable();
            }
            if (! Schema::hasColumn('libraries', 'genre')) {
                $table->text('genre')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('libraries')) {
            return;
        }

        Schema::table('libraries', function (Blueprint $table) {
            if (Schema::hasColumn('libraries', 'genre')) {
                $table->dropColumn('genre');
            }
            if (Schema::hasColumn('libraries', 'language')) {
                $table->dropColumn('language');
            }
            if (Schema::hasColumn('libraries', 'subtitle')) {
                $table->dropColumn('subtitle');
            }
        });
    }
};
