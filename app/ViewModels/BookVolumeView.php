<?php

namespace App\ViewModels;

final class BookVolumeView
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly ?string $subtitle,
        public readonly array $authors,
        public readonly ?string $authorsLine,
        public readonly ?string $thumbnailUrl,
        public readonly ?string $published,
        public readonly ?string $publisher,
        public readonly ?int $pageCount,
        public readonly ?string $language,
        public readonly ?string $genre,
        public readonly ?string $isbn,
        public readonly ?string $description,
        public readonly ?string $previewUrl,
    ) {}

    /**
     * Back-compat for older Blade/tests that used {@see $authorsLine} as a single "author" field.
     */
    public function __get(string $name): mixed
    {
        if ($name === 'author') {
            return $this->authorsLine;
        }

        throw new \Error('Undefined property: '.self::class.'::$'.$name);
    }

    /**
     * Prefer the largest cover Google exposes (search often only had thumbnail before projection=full).
     *
     * @param  array<string, mixed>  $imageLinks
     */
    public static function bestCoverFromImageLinks(array $imageLinks): ?string
    {
        $order = ['extraLarge', 'large', 'medium', 'small', 'thumbnail', 'smallThumbnail'];
        foreach ($order as $key) {
            $url = $imageLinks[$key] ?? null;
            if (is_string($url) && $url !== '') {
                return str_replace('http://', 'https://', $url);
            }
        }

        return null;
    }

    public static function fromApiItem(array $item): self
    {
        $info = $item['volumeInfo'] ?? [];
        $thumb = self::bestCoverFromImageLinks($info['imageLinks'] ?? []);
        $authors = $info['authors'] ?? [];
        $preview = $info['previewLink'] ?? null;
        if ($preview !== null) {
            $preview = str_replace('http://', 'https://', $preview);
        }

        $categories = $info['categories'] ?? [];
        $genre = is_array($categories) && $categories !== []
            ? implode(', ', $categories)
            : null;

        $pageCount = $info['pageCount'] ?? null;
        $pageCount = is_numeric($pageCount) ? (int) $pageCount : null;

        $isbn = null;
        foreach ($info['industryIdentifiers'] ?? [] as $identifier) {
            if (! is_array($identifier)) {
                continue;
            }
            $type = $identifier['type'] ?? '';
            if (in_array($type, ['ISBN_13', 'ISBN_10'], true)) {
                $isbn = (string) ($identifier['identifier'] ?? '');
                break;
            }
        }

        return new self(
            id: (string) ($item['id'] ?? ''),
            title: $info['title'] ?? 'Untitled',
            subtitle: isset($info['subtitle']) ? (string) $info['subtitle'] : null,
            authors: $authors,
            authorsLine: $authors === [] ? null : implode(', ', $authors),
            thumbnailUrl: $thumb,
            published: $info['publishedDate'] ?? null,
            publisher: isset($info['publisher']) ? (string) $info['publisher'] : null,
            pageCount: $pageCount,
            language: isset($info['language']) ? (string) $info['language'] : null,
            genre: $genre,
            isbn: $isbn,
            description: isset($info['description']) ? strip_tags((string) $info['description']) : null,
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
