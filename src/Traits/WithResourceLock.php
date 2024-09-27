<?php

declare(strict_types=1);

namespace ForestLynx\MoonShine\Traits;

use MoonShine\UI\Fields\Preview;
use MoonShine\Support\Enums\Layer;
use Illuminate\Database\Eloquent\Model;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\ActionButton;
use ForestLynx\MoonShine\Components\Modal;
use MoonShine\Laravel\Components\Fragment;
use MoonShine\UI\Components\Table\TableBuilder;
use ForestLynx\MoonShine\Services\ModelRelatedLock;
use MoonShine\Laravel\Collections\Fields;
use MoonShine\UI\Collections\ActionButtons;

trait WithResourceLock
{
    //TODO контроль при редактировании в таблице в режиме updateOnPreview()
    //TODO поддержка карточек товара на индексной странице
    //TODO разблокировка ресурса при закрытии вкладки или переходе на другую страницу

    protected function bootWithResourceLock(): void
    {
        if ($this->getFormPage()) {
            $this->handleUpdateForm();
        }

        if ($this->isDisplayOnIndexPage()) {
            $this->handleIndexPage();
        }
    }

    protected function handleIndexPage(): void
    {
        $this->getIndexPage()
        ->getComponents()
        ->map(
            function ($component) {
                if ($component instanceof Fragment && $component->getName() === 'crud-list') {
                    $this->addResourceLockColumnToTable($component);
                }
            }
        );
    }

    protected function addResourceLockColumnToTable($component): void
    {
        $component->getComponents()->each(
            function ($index) {
                if ($index instanceof TableBuilder) {
                    $index->buttons($this->transformRowButtons($this->getIndexButtons()));
                    $index->fields($this->transformFields($index->getFields()));
                }
            }
        );
    }

    protected function transformRowButtons(ActionButtons $buttons): ActionButtons
    {
        $buttons->each(
            fn(ActionButton $btn): ActionButton =>
            $btn->getName() === 'edit-button' || $btn->getName() === 'delete-button'
            ? $btn->canSee(fn(Model $item, $b): bool => !ModelRelatedLock::make($item)->isLocked())
            : $btn
        );
        $buttons->add(
            ActionButton::make('', '#')
                ->canSee(fn(Model $item, $b): bool => ModelRelatedLock::make($item)->isLocked())
                ->inModal(
                    title: static fn () => __('resource-lock::ui.title'),
                    content: fn() => $this->getPreview()
                )
                ->warning()
                ->icon('lock-closed')
        );

        return $buttons;
    }

    protected function transformFields(Fields $fields): Fields
    {
        return $fields->add(Preview::make(
            label: __('resource-lock::ui.table_title'),
            column: 'resourceLock.id',
            formatted: fn(Model $item): bool => !ModelRelatedLock::make($item)->isLocked()
        )->boolean());
    }

    protected function handleUpdateForm(): void
    {
        if ($this?->getItem()) {
            $modelLock = ModelRelatedLock::make($this->getItem());
            if ($modelLock->isLocked()) {
                $this->handleLockedResource();
            }
            if (!$modelLock->isResourceLock()) {
                $modelLock->lock();
            }
        }
    }

    protected function handleLockedResource(): void
    {
        $this->getFormPage()->pushToLayer(
            Layer::BOTTOM,
            $this->getModal()
        );
    }

    protected function getResourceLockOwner(): ?string
    {
        if (config('resource-lock.show_owner_modal')) {
            return app(config('resource-lock.resource_lock_owner'))
            ->execute(ModelRelatedLock::make($this->getItem())->getResourceLockOwner());
        }
        return null;
    }

    protected function getModal(): Modal
    {
        return Modal::make(
            title: static fn () => __('resource-lock::ui.title'),
            components: [
                $this->getPreview(),
                Flex::make([
                    ActionButton::make(
                        label: __('resource-lock::ui.back_btn'),
                        url: $this->getReturnUrlResourceLock(),
                    )->info()->icon('arrow-uturn-left')
                ])->justifyAlign('start')->itemsAlign('start')
            ]
        )->name('resource-lock-modal');
    }

    protected function getPreview(): Preview
    {
        $content = config('resource-lock.show_owner_modal')
        ? "{$this->getResourceLockOwner()} " . __('resource-lock::ui.locked_notice_user')
        : __('resource-lock::ui.locked_notice');
        return Preview::make(
            formatted: static fn(): string => $content
        )->customAttributes(['class' => 'mb-4']);
    }

    protected function afterUpdated(mixed $item): mixed
    {
        if ($item instanceof Model) {
            ModelRelatedLock::make($item)->unlock();
        }
        return parent::afterUpdated($item);
    }

    protected function getReturnUrlResourceLock(): string
    {
        return $this->getIndexPageUrl();
    }

    protected function isDisplayOnIndexPage(): bool
    {
        $config = config('resource-lock.resource_lock_to_index_page') ?? null;
        return isset($config) ? $config : true;
    }
}
