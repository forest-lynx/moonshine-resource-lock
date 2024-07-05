<?php

declare(strict_types=1);

namespace ForestLynx\MoonShine\Providers;

use Illuminate\Support\ServiceProvider;
use ForestLynx\MoonShine\Commands\InstallCommand;
use ForestLynx\MoonShine\Commands\ClearOldLocking;

final class ResourceLockServiceProvider extends ServiceProvider
{
    protected array $commands = [
        InstallCommand::class,
        ClearOldLocking::class
    ];

    //TODO реализовать очистку устаревших блокировок
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'moonshine-fl');

        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'resource-lock');

        $this->publishes([
            __DIR__ . '/../../resources/lang' => $this->app->langPath('vendor/resource-lock'),
        ], 'resource-lock-lang');

        $this->publishes([
            __DIR__ . '/../../config/resource-lock.php' => config_path('resource-lock.php'),
        ], 'resource-lock-config');

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/resource-lock.php',
            'resource-lock'
        );

        if ($this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }
}
