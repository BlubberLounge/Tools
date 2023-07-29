<?php

namespace App\View\Components\form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InputText extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $attribute,
        public string $label = '',
        public bool $autofocus = false,
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $this->label = $this->label ?? $this->attribute;

        return view('components.form.input-text');
    }
}
