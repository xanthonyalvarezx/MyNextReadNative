<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('libraries')) {
            return;
        }

        Schema::create('libraries', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author')->nullable();
            $table->string('isbn')->nullable();
            $table->string('publisher')->nullable();
            $table->string('publication_date')->nullable();
            $table->unsignedInteger('pages')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('shelf')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('libraries');
    }
};
