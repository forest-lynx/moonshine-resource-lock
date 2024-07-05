<?php

declare(strict_types=1);

namespace ForestLynx\MoonShine\Commands;

use ForestLynx\MoonShine\Models\ResourceLock;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

use function Laravel\Prompts\{intro, form, info, text, confirm, error, select};

#[AsCommand(name: 'resource-lock:clear-old')]
class ClearOldLocking extends Command
{
    protected $signature = 'resource-lock:clear-old';

    protected $description = 'Clearing the database table of outdated resource locks';

    public function handle(): int
    {
        intro('Starting table cleanup...');

        try {
            ResourceLock::where('expired_at', '<', date('Y-m-d H:i:s'))->delete();
            info('Deleting outdated resource lock records was successful.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            error('Unfortunately, the command was not executed successfully.');
            return self::FAILURE;
        }
    }
}
