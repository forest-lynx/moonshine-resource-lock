<?php

declare(strict_types=1);

namespace ForestLynx\MoonShine\Traits;

use MoonShine\Fields\Preview;
use MoonShine\Decorations\Flex;
use MoonShine\Decorations\Fragment;
use MoonShine\Pages\PageComponents;
use MoonShine\Components\TableBuilder;
use Illuminate\Database\Eloquent\Model;
use MoonShine\ActionButtons\ActionButton;
use ForestLynx\MoonShine\Components\Modal;
use ForestLynx\MoonShine\Services\ModelRelatedLock;

trait WithResourceLock
{
    //TODO контроль при редактировании в таблице в режиме updateOnPreview()
    //TODO поддержка карточек товара на индексной странице
    protected ?ModelRelatedLock $modelLock = null;

    protected function bootWithResourceLock(): void
    {
        if (
            $this->isNowOnIndex()
            && config('resource-lock.resource_lock_to_index_page')
        ) {
            $this->handleIndexPage();
        }
        if ($this->isNowOnUpdateForm()) {
            $this->handleUpdateForm();
        }
    }

    protected function handleIndexPage(): void
    {
        $this->indexPage()
        ->getComponents()
        ->map(
            function ($component) {
                if ($component instanceof Fragment && $component->componentName === 'crud-list') {
                    $this->addResourceLockColumnToTable($component);
                }
            }
        );
    }

    protected function addResourceLockColumnToTable($component): void
    {
        $component->getFields()->each(
            function ($index) {
                if ($index instanceof TableBuilder) {
                    $index->fields([
                        ...$index->getFields(),
                        Preview::make(
                            label: __('resource-lock::ui.table_title'),
                            column: 'resourceLock.id',
                            formatted: fn($item): bool => !ModelRelatedLock::make($item)->isLocked()
                        )->boolean()
                    ]);
                }
            }
        );
    }

    protected function handleUpdateForm(): void
    {
        $this->modelLock = ModelRelatedLock::make($this->getItem());
        if ($this->modelLock->isLocked()) {
            $this->handleLockedResource();
        }
        if (!$this->modelLock->isResourceLock()) {
            $this->modelLock->lock();
        }
    }

    protected function handleLockedResource(): void
    {
        $this->formPage()
            ->getComponents()
            ->map(function ($component) {
                if ($component instanceof Fragment && $component->componentName === 'crud-form') {
                    $fields = $this->isEditInModal()
                        ? [$this->getPreview()->badge('red')]
                        : [
                            ...$component->getFields(),
                            $this->getModal()
                        ];
                    $component->fields($fields);
                }
            });
    }

    protected function getResourceLockOwner(): ?string
    {
        if (config('resource-lock.show_owner_modal')) {
            return app(config('resource-lock.resource_lock_owner'))
            ->execute($this->modelLock->getResourceLockOwner());
        }
    }

    protected function getModal(): Modal
    {
            return Modal::make(
                title: static fn () => __('resource-lock::ui.title'),
                components: PageComponents::make([
                $this->getPreview(),
                Flex::make([
                    ActionButton::make(
                        label: __('resource-lock::ui.back_btn'),
                        url: $this->getReturnUrlResourceLock(),
                    )->info()->icon('heroicons.outline.arrow-uturn-left')
                ])->justifyAlign('start')->itemsAlign('start')
                ])
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

    public function getReturnUrlResourceLock(): string
    {
            return $this->indexPageUrl();
    }

    protected function afterUpdated(Model $item): Model
    {
            $this->modelLock->unlock();
            return parent::afterUpdated($item);
    }
}
