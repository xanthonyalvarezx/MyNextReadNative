<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class library extends Model
{
    protected $table = 'libraries';

    protected $fillable = [
        'title',
        'subtitle',
        'author',
        'language',
        'genre',
        'isbn',
        'publisher',
        'publication_date',
        'pages',
        'pages_read',
        'cover_image',
        'description',
        'shelf',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Whole-book progress from pages_read / pages (0–100), or null if total pages unknown.
     */
    public function percentRead(): ?int
    {
        $total = $this->pages;
        if ($total === null || (int) $total < 1) {
            return null;
        }

        $read = (int) ($this->pages_read ?? 0);

        return (int) max(0, min(100, (int) round(($read / (int) $total) * 100)));
    }
}
