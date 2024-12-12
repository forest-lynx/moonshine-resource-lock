<?php

declare(strict_types=1);

namespace ForestLynx\MoonShine\Traits;

use MoonShine\UI\Fields\Preview;
use MoonShine\Support\Enums\Layer;
use Illuminate\Database\Eloquent\Model;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\Laravel\Collections\Fields;
use MoonShine\UI\Components\ActionButton;
use ForestLynx\MoonShine\Components\Modal;
use MoonShine\Laravel\Components\Fragment;
use MoonShine\UI\Collections\ActionButtons;
use MoonShine\Contracts\UI\FormElementContract;
use MoonShine\UI\Components\Table\TableBuilder;
use ForestLynx\MoonShine\Services\ModelRelatedLock;

trait WithResourceLock
{
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
            ->filter(fn($component) => $component instanceof Fragment && $component->getName() === 'crud-list')
            ->each(fn($component) => $this->addResourceLockColumnToTable($component));
    }

    protected function addResourceLockColumnToTable($component): void
    {
        $component->getComponents()
            ->filter(fn($index) => $index instanceof TableBuilder)
            ->each(function (TableBuilder $index) {
                $index->buttons($this->transformRowButtons($this->getIndexButtons()));
                $index->fields($this->transformFields($index->getFields()));
            });
    }

    protected function transformRowButtons(ActionButtons $buttons): ActionButtons
    {
        $buttons->transform(function (ActionButton $btn): ActionButton {
            if (in_array($btn->getName(), ['resource-edit-button', 'resource-delete-button'])) {
                return $btn->canSee(fn(Model $item): bool => !ModelRelatedLock::make($item)->isLocked());
            }
            return $btn;
        });

        $buttons->add($this->buttonInfo());

        return $buttons;
    }

    protected function buttonInfo(): ActionButton
    {
        return ActionButton::make('', '#')
            ->canSee(fn(Model $item): bool => ModelRelatedLock::make($item)->isLocked())
            ->inModal(
                title: static fn () => __('resource-lock::ui.title'),
                content: fn(Model $item) => $this->getPreview($item),
            )
            ->warning()
            ->icon('lock-closed');
    }

    protected function transformFields(Fields $fields): Fields
    {
        return $fields->transform(fn (FormElementContract $field): FormElementContract =>
                ($this->needsTransform($field))
                ? $this->transformField($field)
                : $field)
            ->add($this->addStatusField());
    }

    private function needsTransform(FormElementContract $field): bool
    {
        return method_exists($field, 'isUpdateOnPreview') && $field->isUpdateOnPreview();
    }

    private function transformField(FormElementContract $field): FormElementContract
    {
        return $field->onBeforeRender(function (FormElementContract $f) {
            $originalData = $f->getData()?->getOriginal();
            if ($originalData && ModelRelatedLock::make($originalData)?->isResourceLock()) {
                return $f->readonly(condition: true);
            }
            return $f;
        });
    }

    private function addStatusField(): Preview
    {
        return Preview::make(
            label: __('resource-lock::ui.table_title'),
            column: 'resourceLock.id',
            formatted: fn(Model $item): bool => !ModelRelatedLock::make($item)->isLocked()
        )->boolean();
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

    protected function getResourceLockOwner(?Model $item = null): ?string
    {
        if (config('resource-lock.show_owner_modal')) {
            return app(config('resource-lock.resource_lock_owner'))
            ->execute(ModelRelatedLock::make($this->getItem() ?? $item)->getResourceLockOwner());
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

    protected function getPreview(?Model $item = null): Preview
    {
        /** @var string $content */
        $content = config('resource-lock.show_owner_modal')
            ? "{$this->getResourceLockOwner($item)} " . __('resource-lock::ui.locked_notice_user')
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
        return config('resource-lock.resource_lock_to_index_page') ?? true;
    }
}
