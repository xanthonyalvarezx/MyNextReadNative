<?php

namespace App\Foundation;

use Illuminate\Foundation\Application as LaravelApplication;

/**
 * Lets Laravel recognize Windows drive-letter paths as absolute for APP_*_CACHE env vars.
 * Without this, "C:\..." is treated as relative to base_path() and breaks NativePHP cache redirects.
 *
 * @see LaravelApplication::normalizeCachePath()
 */
class Application extends LaravelApplication
{
    public function __construct(?string $basePath = null)
    {
        if (PHP_OS_FAMILY === 'Windows') {
            foreach (range('A', 'Z') as $letter) {
                $this->addAbsoluteCachePathPrefix($letter.':');
            }
        }

        parent::__construct($basePath);
    }
}
