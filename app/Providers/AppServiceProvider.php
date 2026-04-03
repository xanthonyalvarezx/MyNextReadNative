<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->booted(function (): void {
            // Prefer getenv: config cache can bake nativephp-internal.running=false even when Electron sets the env.
            if (getenv('NATIVEPHP_RUNNING') !== 'true') {
                return;
            }

            if (! is_array(config('database.connections.nativephp'))) {
                return;
            }

            try {
                if (! Schema::connection('nativephp')->hasTable('libraries')) {
                    Artisan::call('migrate', [
                        '--database' => 'nativephp',
                        '--force' => true,
                    ]);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
