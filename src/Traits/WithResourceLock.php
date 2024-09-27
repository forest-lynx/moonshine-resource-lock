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
use MoonShine\Enums\Layer;
use MoonShine\Enums\PageType;

trait WithResourceLock
{
    //TODO контроль при редактировании в таблице в режиме updateOnPreview()
    //TODO поддержка карточек товара на индексной странице
    //TODO разблокировка ресурса при закрытии вкладки или переходе на другую страницу

    protected function bootWithResourceLock(): void
    {
        if (
            $this->isNowOnIndex()
            && $this->isDisplayOnIndexPage()
        ) {
            $this->handleIndexPage();
        }
        if ($this->isNowOnUpdateForm()) {
            $this->handleUpdateForm();
        }
    }

    public function getIndexItemButtons(): array
    {
        return [
            ...$this->getIndexButtons(),
            $this->getDetailButton(
                isAsync: $this->isAsync()
            ),
            $this->getEditButton(
                isAsync: $this->isAsync()
            )->canSee(fn(Model $item, $b): bool => !ModelRelatedLock::make($item)->isLocked()),
            $this->getDeleteButton(
                redirectAfterDelete: $this->redirectAfterDelete(),
                isAsync: $this->isAsync()
            )->canSee(fn(Model $item, $b): bool => !ModelRelatedLock::make($item)->isLocked()),
            $this->getPreviewButton(),
            $this->getMassDeleteButton(
                redirectAfterDelete: $this->redirectAfterDelete(),
                isAsync: $this->isAsync()
            ),
        ];
    }

    protected function getPreviewButton(): ActionButton
    {
        return ActionButton::make('', '#')
                ->canSee(fn(Model $item, $b): bool => ModelRelatedLock::make($item)->isLocked())
                ->inModal(
                    title: static fn () => __('resource-lock::ui.title'),
                    content: fn() => $this->getPreview()
                )
                ->warning()
                ->icon('heroicons.outline.lock-closed');
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
        $modelLock = ModelRelatedLock::make($this->getItem());
        if ($modelLock->isLocked()) {
            $this->handleLockedResource();
        }
        if (!$modelLock->isResourceLock()) {
            $modelLock->lock();
        }
    }

    protected function handleLockedResource(): void
    {
        $this->getPages()
            ->findByType(PageType::FORM)
            ->pushToLayer(
                Layer::BOTTOM,
                $this->getModal()
            );
    }

    protected function getResourceLockOwner(): ?string
    {
        if (!$this?->getItem()) {
            return null;
        }
        if (config('resource-lock.show_owner_modal')) {
            return app(config('resource-lock.resource_lock_owner'))
            ->execute(ModelRelatedLock::make($this->getItem())->getResourceLockOwner());
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

    protected function afterUpdated(Model $item): Model
    {
        ModelRelatedLock::make($item)->unlock();
        return parent::afterUpdated($item);
    }

    protected function getReturnUrlResourceLock(): string
    {
        return $this->indexPageUrl();
    }

    protected function isDisplayOnIndexPage(): bool
    {
        $config = config('resource-lock.resource_lock_to_index_page') ?? null;
        return isset($config) ? $config : true;
    }
}
