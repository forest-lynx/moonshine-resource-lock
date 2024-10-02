<?php

declare(strict_types=1);

namespace ForestLynx\MoonShine\Resources;

use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Text;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Laravel\QueryTags\QueryTag;
use ForestLynx\MoonShine\Models\ResourceLock;
use MoonShine\Laravel\Resources\ModelResource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Laravel\Enums\Action;

/**
 * @extends ModelResource<ResourceLock>
 */
class LockResource extends ModelResource
{
    protected string $model = ResourceLock::class;

    protected array $with = ['user'];

    public function __construct()
    {
        $this->title = __('resource-lock::ui.lock_resource_title');
    }

    public function indexFields(): array
    {
        return [
            ID::make('id'),
            Text::make(
                label: __('resource-lock::ui.owner'),
                column: 'owner',
                formatted: fn(Model $item): string => app(config('resource-lock.resource_lock_owner'))->execute($item->user)
            ),
            Text::make(
                label: __('resource-lock::ui.lockable_type'),
                column: 'lockable_type'
            )->sortable(),
            Text::make(
                label: __('resource-lock::ui.lockable_id'),
                column: 'lockable_id'
            )->sortable(),
            Date::make(
                label: __('resource-lock::ui.locking_at'),
                column: 'locking_at',
            )->sortable(),
            Date::make(
                label: __('resource-lock::ui.expired_at'),
                column: 'expired_at',
            )->badge(fn($v, $f): string => $f->getData()->getOriginal()->isExpired() ? 'green' : 'red')
            ->sortable(),
        ];
    }

    public function getActiveActions(): array
    {
        return [Action::DELETE, Action::MASS_DELETE];
    }

    public function queryTags(): array
    {
        return [
           QueryTag::make(
               __('resource-lock::ui.query_tag_locked'),
               fn(Builder $query) => $query->where('expired_at', '>=', date('Y-m-d H:i:s'))
           )->icon('lock-closed'),
           QueryTag::make(
               __('resource-lock::ui.query_tag_unlocked'),
               fn(Builder $query) => $query->where('expired_at', '<', date('Y-m-d H:i:s'))
           )->icon('lock-open'),
        ];
    }

    public function rules(mixed $item): array
    {
        return [];
    }
}
