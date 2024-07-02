<?php

declare(strict_types=1);

namespace ForestLynx\MoonShine\Services;

use MoonShine\MoonShineAuth;
use MoonShine\Traits\Makeable;
use Illuminate\Database\Eloquent\Model;
use ForestLynx\MoonShine\Models\ResourceLock;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\MorphOne;

final class ModelRelatedLock
{
    //TODO подумать о необходимости блокировки записи в базе данных
    use Makeable;

    protected Model|Authenticatable $authUser;

    public function __construct(protected Model $model)
    {
        $this->authUser = MoonShineAuth::guard()->user();
        $model::resolveRelationUsing('resourceLock', function (Model $model): MorphOne {
            return $model->morphOne(ResourceLock::class, 'lockable');
        });

        $model->load('resourceLock');
    }

    public function isResourceLock(): bool
    {
        if (! $this->model->resourceLock) {
            return false;
        }

        return $this->model->resourceLock->exists()
            && !$this->model->resourceLock->isExpired();
    }

    public function isLocked(): bool
    {
        return $this->isResourceLock()
            && !$this->isLockedByCurrentUser();
    }

    public function isLockedByCurrentUser(): bool
    {
        return $this->authUser->id === $this->model->resourceLock?->user_id;
    }

    public function lock(): bool
    {
        if ($this->isResourceLock()) {
            return false;
        }

        $resourceLock = new ResourceLock();
        $resourceLock->lockable()->associate($this->model);
        $resourceLock->user()->associate($this->authUser);
        $resourceLock->save();
        return true;
    }

    public function unlock(): bool
    {
        if ($this->isResourceLock() && $this->isLockedByCurrentUser()) {
            $this->model->resourceLock()->delete();
            return true;
        }

        return false;
    }

    public function getResourceLockOwner(): Model
    {
        return $this->model->resourceLock->user;
    }
}
