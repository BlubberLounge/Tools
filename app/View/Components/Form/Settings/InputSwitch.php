<?php

namespace App\View\Components\Form\Settings;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InputSwitch extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $id = '',
        public string $label = '',
        public string $description = '',
    ){ }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.form.settings.input-switch');
    }
}
