<?php

namespace App\Support;

use App\Models\library as LibraryModel;

final class LibraryGenrePie
{
    /** @var list<string> */
    private const SLICE_COLORS = [
        'color-mix(in srgb, var(--color-teal) 72%, var(--color-surface))',
        'color-mix(in srgb, var(--color-indigo) 58%, var(--color-surface))',
        'color-mix(in srgb, var(--color-slate) 70%, var(--color-surface))',
        'color-mix(in srgb, var(--color-taupe) 55%, var(--color-surface))',
        'color-mix(in srgb, var(--color-teal) 45%, var(--color-indigo) 55%)',
        'color-mix(in srgb, var(--color-slate) 50%, var(--color-indigo) 50%)',
    ];

    /**
     * Primary genre = first comma-separated category (Google Books style).
     * Up to ($maxSlices - 1) distinct genres, remainder rolled into "Other".
     *
     * @return array{
     *     slices: list<array{label: string, pct: float, pct_display: string, color: string}>,
     *     gradient: string,
     *     aria_label: string
     * }|null
     */
    public static function fromDatabase(int $maxSlices = 6): ?array
    {
        $genres = LibraryModel::query()
            ->whereNotNull('genre')
            ->pluck('genre');

        /** @var array<string, array{label: string, count: int}> */
        $byKey = [];

        foreach ($genres as $raw) {
            if (! is_string($raw) || trim($raw) === '') {
                continue;
            }
            $parts = array_values(array_filter(array_map('trim', explode(',', $raw))));
            $primary = $parts[0] ?? null;
            if ($primary === null || $primary === '') {
                continue;
            }

            $key = mb_strtolower($primary);
            if (! isset($byKey[$key])) {
                $byKey[$key] = [
                    'label' => $primary,
                    'count' => 0,
                ];
            }
            $byKey[$key]['count']++;
        }

        if ($byKey === []) {
            return null;
        }

        uasort($byKey, fn (array $a, array $b): int => $b['count'] <=> $a['count']);
        $ordered = array_values($byKey);

        if (count($ordered) > $maxSlices) {
            $headCount = $maxSlices - 1;
            $head = array_slice($ordered, 0, $headCount);
            $tail = array_slice($ordered, $headCount);
            $otherCount = 0;
            foreach ($tail as $row) {
                $otherCount += $row['count'];
            }
            $ordered = array_merge($head, [
                ['label' => 'Other', 'count' => $otherCount],
            ]);
        }

        $total = 0;
        foreach ($ordered as $row) {
            $total += $row['count'];
        }

        if ($total === 0) {
            return null;
        }

        $slices = [];
        $gradientParts = [];
        $cum = 0.0;
        $ariaParts = [];

        foreach ($ordered as $i => $row) {
            $fraction = $row['count'] / $total;
            $pct = round($fraction * 100, 1);
            $color = self::SLICE_COLORS[$i % count(self::SLICE_COLORS)];

            $start = $cum;
            $cum += $fraction * 100;
            $end = $cum;

            $gradientParts[] = sprintf(
                '%s %.4f%% %.4f%%',
                $color,
                $start,
                $end
            );

            $pctDisplay = $pct == floor($pct) ? (string) (int) $pct : (string) $pct;

            $slices[] = [
                'label' => $row['label'],
                'pct' => $pct,
                'pct_display' => $pctDisplay,
                'color' => $color,
            ];

            $ariaParts[] = sprintf('%s %s%%', $row['label'], $pctDisplay);
        }

        $gradient = 'conic-gradient(from -90deg, '.implode(', ', $gradientParts).')';

        return [
            'slices' => $slices,
            'gradient' => $gradient,
            'aria_label' => 'Genre pie chart: '.implode(', ', $ariaParts),
        ];
    }
}
