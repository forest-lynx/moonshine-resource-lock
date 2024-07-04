<?php

declare(strict_types=1);

namespace ForestLynx\MoonShine\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

use function Laravel\Prompts\{intro, form, info, text, confirm, select};

#[AsCommand(name: 'resource-lock:install')]
class InstallCommand extends Command
{
    protected $signature = 'resource-lock:install';

    protected $description = 'Installing the resource-lock package for the MoonShine admin panel.';

    public function handle(): int
    {
        intro('Installing resource-lock package...');

        $this->migration();

        $publishConfig = confirm(
            label: 'Do you want to publish the config file?',
            default: false
        );

        if ($publishConfig) {
            $this->call('vendor:publish', [
                '--tag' => 'resource-lock-config'
            ]);
        }

        $publishLangFile = confirm(
            label: 'Do you want to publish a language file?',
            default: false
        );

        if ($publishLangFile) {
            $this->call('vendor:publish', [
                '--tag' => 'resource-lock-lang'
            ]);
        }

        info('resource-lock package installed successfully.');

        return self::SUCCESS;
    }

    protected function migration(): void
    {
        $this->call('migrate', [
           '--path' => 'vendor/forest-lynx/moonshine-resource-lock/database/migrations/create_resource_lock_table.php'
        ]);
    }
}
