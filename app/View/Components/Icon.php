<?php

namespace App\View\Components;

use App\Enums\Heroicon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Icon extends Component
{
    public Heroicon $icon;

    public function __construct(
        public string|Heroicon $name,
        public string|int $size = 24,
        public ?string $class = null
    ) {
        $this->icon = is_string($name) ? Heroicon::fromName($name) : $name;

        if (! $this->icon) {
            throw new \InvalidArgumentException("Icon '{$name}' not found");
        }
    }

    public function render(): View|Closure|string
    {
        return $this->icon->getSvg($this->size, $this->class);
    }
}
