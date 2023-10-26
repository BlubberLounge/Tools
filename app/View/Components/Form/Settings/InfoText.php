<?php

namespace App\View\Components\Form\Settings;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InfoText extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $description = ''
    ) { }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.form.settings.info-text');
    }
}
