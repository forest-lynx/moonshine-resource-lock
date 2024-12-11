<?php

declare(strict_types=1);

namespace ForestLynx\MoonShine\Providers;

use Illuminate\Support\ServiceProvider;
use ForestLynx\MoonShine\Commands\InstallCommand;
use ForestLynx\MoonShine\Commands\ClearOldLocking;
use ForestLynx\MoonShine\Resources\LockResource;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Contracts\MenuManager\MenuManagerContract;

final class ResourceLockServiceProvider extends ServiceProvider
{
    //TODO реализовать очистку устаревших блокировок
    //TODO Реализовать настройку отображения меню в автоматическом режиме в зависимости от настроек пакета
    protected array $commands = [
        InstallCommand::class,
        ClearOldLocking::class
    ];

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

    /*protected function addMenu(
        CoreContract $core,
        MenuManagerContract $menu
    ): void {
        $core->resources([
            LockResource::class,
        ]);
    }*/
}
