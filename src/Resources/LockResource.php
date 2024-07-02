<?php

declare(strict_types=1);

namespace ForestLynx\MoonShine\Resources;

use App\Models\Address;
use MoonShine\Fields\ID;
use MoonShine\Fields\Date;
use MoonShine\Fields\Text;
use MoonShine\Handlers\ExportHandler;
use MoonShine\Handlers\ImportHandler;
use MoonShine\Resources\ModelResource;
use Illuminate\Database\Eloquent\Model;
use ForestLynx\MoonShine\Models\ResourceLock;

/**
 * @extends ModelResource<Address>
 */
class LockResource extends ModelResource
{
    //TODO добавить разблокировку ресурса
    //TODO проверка прав
    //TODO добавление в меню
    protected string $model = ResourceLock::class;

    protected array $with = ['user'];

    public function title(): string
    {
        return __('resource-lock::ui.lock_resource_title');
    }

    public function fields(): array
    {
        return [
            ID::make('id'),
            Text::make(
                label: __('resource-lock::ui.owner'),
                column: 'owner',
                formatted: fn($item): string => app(config('resource-lock.resource_lock_owner'))
                ->execute($item->user)
            ),
            Text::make(
                label: __('resource-lock::ui.lockable_type'),
                column: 'lockable_type'
            ),
            Text::make(
                label: __('resource-lock::ui.lockable_id'),
                column: 'lockable_id'
            ),
            Date::make(
                label: __('resource-lock::ui.locking_at'),
                column: 'locking_at',
            ),
            Date::make(
                label: __('resource-lock::ui.expired_at'),
                column: 'expired_at',
            )->badge(fn($v, $f): string => $f->getData()->isExpired() ? 'green' : 'red'),
        ];
    }

    public function rules(Model $item): array
    {
        return [];
    }

    public function search(): array
    {
        return [];
    }

    public function getActiveActions(): array
    {
        return [];
    }

    public function import(): ?ImportHandler
    {
        return null;
    }

    public function export(): ?ExportHandler
    {
        return null;
    }
}
