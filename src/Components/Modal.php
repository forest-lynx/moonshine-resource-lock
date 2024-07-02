<?php

declare(strict_types=1);

namespace ForestLynx\MoonShine\Components;

use Closure;
use MoonShine\Support\Condition;
use Illuminate\View\ComponentSlot;
use Illuminate\Contracts\View\View;
use MoonShine\Components\MoonShineComponent;
use MoonShine\Collections\MoonShineRenderElements;
use MoonShine\Components\Components;

/**
 * @method static static make(Closure|string $title, Closure|View|string $content,  MoonShineRenderElements|null $components = null)
 */
final class Modal extends MoonShineComponent
{
    protected string $view = 'moonshine-fl::components.modal';

    protected bool $wide = false;

    protected bool $auto = false;

    public function __construct(
        protected Closure|string $title = '',
        protected Closure|string $content = '',
        protected ?MoonShineRenderElements $components = null
    ) {
    }

    public function wide(Closure|bool|null $condition = null): self
    {
        $this->wide = is_null($condition) || Condition::boolean($condition, false);

        return $this;
    }

    public function auto(Closure|bool|null $condition = null): self
    {
        $this->auto = is_null($condition) || Condition::boolean($condition, false);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    protected function viewData(): array
    {
        $componentsHtml = $this->components?->isNotEmpty() ?
            Components::make($this->components) : '' ;

        return [
            'title' => value($this->title, $this),
            'slot' => new ComponentSlot(value($this->content, $this) . $componentsHtml),
            'isWide' => $this->wide,
            'isAuto' => $this->auto,
        ];
    }
}
