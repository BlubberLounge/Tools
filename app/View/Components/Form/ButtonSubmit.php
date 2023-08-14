<?php

namespace App\View\Components\Form;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ButtonSubmit extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $label = 'Save'
    ) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $this->label = __($this->label);
        return view('components.form.button-submit');
    }
}
