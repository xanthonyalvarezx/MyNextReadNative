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
            if (! Schema::hasColumn('libraries', 'pages_read')) {
                $table->unsignedInteger('pages_read')->default(0)->after('pages');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('libraries')) {
            return;
        }

        Schema::table('libraries', function (Blueprint $table) {
            if (Schema::hasColumn('libraries', 'pages_read')) {
                $table->dropColumn('pages_read');
            }
        });
    }
};
