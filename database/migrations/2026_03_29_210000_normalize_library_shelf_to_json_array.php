<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('libraries')) {
            return;
        }

        foreach (DB::table('libraries')->cursor() as $row) {
            $raw = $row->shelf;
            if ($raw === null || $raw === '') {
                continue;
            }

            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $normalized = array_values(array_unique(array_map('strval', $decoded)));
                DB::table('libraries')->where('id', $row->id)->update([
                    'shelf' => json_encode($normalized),
                ]);

                continue;
            }

            DB::table('libraries')->where('id', $row->id)->update([
                'shelf' => json_encode([(string) $raw]),
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('libraries')) {
            return;
        }

        foreach (DB::table('libraries')->cursor() as $row) {
            $raw = $row->shelf;
            if ($raw === null || $raw === '') {
                continue;
            }

            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && $decoded !== []) {
                DB::table('libraries')->where('id', $row->id)->update([
                    'shelf' => (string) reset($decoded),
                ]);
            }
        }
    }
};
