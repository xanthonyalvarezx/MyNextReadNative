<?php

declare(strict_types=1);
use App\Foundation\Application;

/**
 * NativePHP Electron ships the app in a read-only or non-writable tree. `php artisan optimize` must
 * write config / route / event caches somewhere writable. Electron sets NATIVEPHP_STORAGE_PATH to
 * per-user app data for that.
 *
 * We intentionally do NOT override APP_SERVICES_CACHE or APP_PACKAGES_CACHE: those should keep using
 * the copies shipped under the app’s bootstrap/cache (read-only is fine). Pointing them at an empty
 * userData folder forces a full manifest rebuild on every fresh install and can prevent the app from
 * booting if that rebuild fails.
 *
 * Windows paths must be plain "C:\..." — PHP's file API often cannot write via the "\\?\" prefix here.
 * {@see Application} registers drive-letter prefixes so Laravel treats them as absolute.
 */
(function (): void {
    $storage = getenv('NATIVEPHP_STORAGE_PATH');
    if ($storage === false || $storage === '') {
        return;
    }

    $dir = rtrim($storage, '\\/').DIRECTORY_SEPARATOR.'bootstrap'.DIRECTORY_SEPARATOR.'cache';
    if (! is_dir($dir) && ! @mkdir($dir, 0755, true)) {
        return;
    }

    if (getenv('NATIVEPHP_RUNNING') !== 'true') {
        return;
    }

    if (! is_writable($dir)) {
        return;
    }

    $set = static function (string $key, string $file) use ($dir): void {
        $path = $dir.DIRECTORY_SEPARATOR.$file;
        putenv("{$key}={$path}");
        $_ENV[$key] = $path;
        $_SERVER[$key] = $path;
    };

    // optimize / config:cache output only — keep services.php & packages.php on the bundled bootstrap path
    $set('APP_CONFIG_CACHE', 'config.php');
    $set('APP_ROUTES_CACHE', 'routes-v7.php');
    $set('APP_EVENTS_CACHE', 'events.php');
})();
