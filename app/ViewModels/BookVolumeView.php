<?php

namespace App\ViewModels;

final class BookVolumeView
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly array $authors,
        public readonly ?string $authorsLine,
        public readonly ?string $thumbnailUrl,
        public readonly ?string $published,
        public readonly ?string $previewUrl,
    ) {}

    public static function fromApiItem(array $item): self
    {
        $info = $item['volumeInfo'] ?? [];
        $thumb = $info['imageLinks']['thumbnail'] ?? $info['imageLinks']['smallThumbnail'] ?? null;
        if ($thumb !== null) {
            $thumb = str_replace('http://', 'https://', $thumb);
        }
        $authors = $info['authors'] ?? [];
        $preview = $info['previewLink'] ?? null;
        if ($preview !== null) {
            $preview = str_replace('http://', 'https://', $preview);
        }

        return new self(
            id: (string) ($item['id'] ?? ''),
            title: $info['title'] ?? 'Untitled',
            authors: $authors,
            authorsLine: $authors === [] ? null : implode(', ', $authors),
            thumbnailUrl: $thumb,
            published: $info['publishedDate'] ?? null,
            previewUrl: $preview,
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return list<self>
     */
    public static function collectFromApiItems(array $items): array
    {
        return array_map(fn (array $item) => self::fromApiItem($item), $items);
    }
}
