<?php

declare(strict_types=1);

namespace ForestLynx\MoonShine\Traits;

use MoonShine\Enums\Layer;
use MoonShine\Enums\PageType;
use MoonShine\Fields\Preview;
use MoonShine\Decorations\Flex;
use MoonShine\Pages\PageComponents;
use MoonShine\ActionButtons\ActionButton;
use ForestLynx\MoonShine\Components\Modal;
use ForestLynx\MoonShine\Services\ModelRelatedLock;
use Illuminate\Database\Eloquent\Model;

trait WithResourceLock
{
    protected ModelRelatedLock $modelLock;

    protected function bootWithResourceLock(): void
    {
        //TODO добавить проверку на форму редактирования после обновы moonshine
        //TODO добавить обработку индексной с добавлением информации о блокировке
        //TODO реализовать отслеживание DOM на предмет удаления модального окна
        //TODO подумать о реализации блокировки при редактировании в модальном окне
        $item = $this->getItem();
        if ($item) {
            $this->modelLock = ModelRelatedLock::make($item);
            if ($this->modelLock->isLocked()) {
                $this->getPages()
                    ->findByUri(PageType::FORM->value)
                    ->pushToLayer(
                        layer: Layer::BOTTOM,
                        component: $this->getModal(),
                    );
            }
            if (!$this->modelLock->isResourceLock()) {
                $this->modelLock->lock();
            }
        }
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
        $content = config('resource-lock.show_owner_modal')
            ? "{$this->getResourceLockOwner()} " . __('resource-lock::ui.locked_notice_user')
            : __('resource-lock::ui.locked_notice');
        return Modal::make(
            title: static fn () => __('resource-lock::ui.title'),
            components: PageComponents::make([
                Preview::make(
                    formatted: static fn(): string => $content
                )->customAttributes(['class' => 'mb-4']),
                Flex::make([
                    ActionButton::make(
                        label: __('resource-lock::ui.back_btn'),
                        url: $this->getReturnUrlResourceLock(),
                    )->info()->icon('heroicons.outline.arrow-uturn-left')
                ])->justifyAlign('start')->itemsAlign('start')
            ])
        );
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
