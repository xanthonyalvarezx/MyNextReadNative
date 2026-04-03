<?php

namespace App\Support;

use App\Models\library as LibraryModel;

final class LibraryReadShelfSizePie
{
    private const PAGE_SPLIT = 500;

    /** @var list<string> */
    private const SLICE_COLORS = [
        'color-mix(in srgb, var(--color-teal) 72%, var(--color-surface))',
        'color-mix(in srgb, var(--color-indigo) 58%, var(--color-surface))',
    ];

    /**
     * Books on the "read" shelf with a known page count: under vs 500+ pages.
     *
     * @return array{
     *     slices: list<array{label: string, pct: float, pct_display: string, color: string}>,
     *     gradient: string,
     *     aria_label: string
     * }|null
     */
    public static function fromDatabase(): ?array
    {
        $under = LibraryModel::query()
            ->where('shelf', 'read')
            ->whereNotNull('pages')
            ->where('pages', '<', self::PAGE_SPLIT)
            ->count();

        $over = LibraryModel::query()
            ->where('shelf', 'read')
            ->whereNotNull('pages')
            ->where('pages', '>=', self::PAGE_SPLIT)
            ->count();

        $ordered = [
            ['label' => 'Under 500 pages', 'count' => $under],
            ['label' => '500 pages or more', 'count' => $over],
        ];

        $total = $under + $over;

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
            'aria_label' => 'Read shelf by length: '.implode(', ', $ariaParts),
        ];
    }
}
