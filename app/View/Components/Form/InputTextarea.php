<?php

namespace App\View\Components\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InputTextarea extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $attribute,
        public string $label = '',
        public bool $autofocus = false,
        public int|null $maxRows = 10,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $this->label = $this->label ?? $this->attribute;

        return view('components.form.input-textarea');
    }
}
