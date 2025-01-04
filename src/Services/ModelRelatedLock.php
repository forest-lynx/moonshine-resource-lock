<?php

declare(strict_types=1);

namespace ForestLynx\MoonShine\Services;

use Illuminate\Support\Str;
use MoonShine\Laravel\MoonShineAuth;
use MoonShine\Support\Traits\Makeable;
use Illuminate\Database\Eloquent\Model;
use ForestLynx\MoonShine\Models\ResourceLock;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\MorphOne;

final class ModelRelatedLock
{
    //TODO подумать о необходимости блокировки записи в базе данных
    use Makeable;

    private Model|Authenticatable $authUser;

    private ?ResourceLock $resourceLock;

    public function __construct(protected Model $model)
    {
        $this->authUser = MoonShineAuth::getGuard()->user();
        $this->addRelation();
        $this->loadResourceLock();
    }

    private function addRelation(): void
    {
        $this->model::resolveRelationUsing('resourceLock', function (Model $model): MorphOne {
            return $model->morphOne(ResourceLock::class, 'lockable');
        });
    }

    private function loadResourceLock(): void
    {
        $this->model->load('resourceLock');
        $this->resourceLock = $this->model->resourceLock;
    }

    public function isResourceLock(): bool
    {
        return $this->resourceLock?->exists() && !$this->resourceLock?->isExpired();
    }

    public function isLocked(): bool
    {
        return $this->isResourceLock() && !$this->isLockedByCurrentUser();
    }

    public function isLockedByCurrentUser(): bool
    {
        $foreignKey = $this->getForeignKeyName();
        return $this->authUser->id === $this->resourceLock?->$foreignKey;
    }

    private function getForeignKeyName(): string
    {
        return \sprintf(
            "%s_%s",
            Str::singular($this->authUser->getTable()),
            $this->authUser->getKeyName()
        );
    }

    public function lock(): bool
    {
        if ($this->isResourceLock()) {
            return false;
        }

        $resourceLock = new ResourceLock();
        $resourceLock->lockable()->associate($this->model);
        $resourceLock->user()->associate($this->authUser);

        return $resourceLock->save();
    }

    public function unlock(): bool
    {
        if (!$this->isResourceLock() || !$this->isLockedByCurrentUser()) {
            return false;
        }

        return (bool) $this->model->resourceLock()->delete();
    }

    public function getResourceLockOwner(): ?Model
    {
        return $this->resourceLock?->user;
    }
}
