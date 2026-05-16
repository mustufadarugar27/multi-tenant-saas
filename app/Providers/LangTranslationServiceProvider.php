<?php


namespace App\Providers;

use App\Support\LangTranslations;
use Illuminate\Support\ServiceProvider;

class LangTranslationServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Skip during artisan commands (migrate, seed, etc.) — DB may not exist yet.
        if ($this->app->runningInConsole()) {
            return;
        }

        LangTranslations::all();
    }
}
