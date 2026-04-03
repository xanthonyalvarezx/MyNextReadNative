<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ORDER = ['read', 'want-to-read', 'reading', 'owned'];

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
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                continue;
            }

            $picked = null;
            foreach (self::ORDER as $key) {
                if (in_array($key, $decoded, true)) {
                    $picked = $key;
                    break;
                }
            }

            if ($picked === null && $decoded !== []) {
                $picked = (string) reset($decoded);
            }

            DB::table('libraries')->where('id', $row->id)->update([
                'shelf' => $picked,
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
            if (is_array($decoded)) {
                continue;
            }

            DB::table('libraries')->where('id', $row->id)->update([
                'shelf' => json_encode([(string) $raw]),
            ]);
        }
    }
};
