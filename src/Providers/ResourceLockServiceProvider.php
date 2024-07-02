<?php

declare(strict_types=1);

namespace ForestLynx\MoonShine\Providers;

use ForestLynx\MoonShine\Commands\InstallCommand;
use ForestLynx\MoonShine\Models\ResourceLock;
use ForestLynx\MoonShine\Observers\ModelObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\ServiceProvider;

final class ResourceLockServiceProvider extends ServiceProvider
{
    protected array $commands = [
        InstallCommand::class
    ];

    //TODO реализовать очистку устаревших блокировок
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'moonshine-fl');

        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'resource-lock');

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
