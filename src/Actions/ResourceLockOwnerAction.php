<?php

declare(strict_types=1);

namespace ForestLynx\MoonShine\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;

class ResourceLockOwnerAction
{
    public function execute(Model|Authenticatable $user): ?string
    {
        return $user->name;
    }
}
