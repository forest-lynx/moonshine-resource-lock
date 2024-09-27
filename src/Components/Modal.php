<?php

declare(strict_types=1);

namespace ForestLynx\MoonShine\Components;

use Closure;
use Illuminate\View\ComponentSlot;
use Illuminate\Contracts\View\View;
use MoonShine\UI\Components\AbstractWithComponents;
use MoonShine\UI\Components\Components;

/**
 * @method static static make(Closure|string $title, Closure|View|string $content,  iterable $components = [])
 */
final class Modal extends AbstractWithComponents
{
    protected string $view = 'moonshine-fl::components.modal';

    protected bool $wide = false;

    protected bool $auto = false;

    public function __construct(
        protected Closure|string $title = '',
        protected Closure|string $content = '',
        protected iterable $components = []
    ) {
        parent::__construct($components);
    }

    public function wide(Closure|bool|null $condition = null): self
    {
        $this->wide = value($condition, $this) ?? false;

        return $this;
    }

    public function auto(Closure|bool|null $condition = null): self
    {
        $this->auto = value($condition, $this) ?? false;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    protected function viewData(): array
    {
        $componentsHtml = $this->getComponents()->isNotEmpty()
            ? Components::make($this->getComponents())
            : '';

        return [
            'title' => value($this->title, $this),
            'slot' => new ComponentSlot(value($this->content, $this) . $componentsHtml),
            'isWide' => $this->wide,
            'isAuto' => $this->auto,
        ];
    }
}
