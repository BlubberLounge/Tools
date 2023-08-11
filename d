[1mdiff --git a/app/View/Components/form/InputCheckbox.php b/app/View/Components/form/InputCheckbox.php[m
[1mdeleted file mode 100644[m
[1mindex f45043a..0000000[m
[1m--- a/app/View/Components/form/InputCheckbox.php[m
[1m+++ /dev/null[m
[36m@@ -1,31 +0,0 @@[m
[31m-<?php[m
[31m-[m
[31m-namespace App\View\Components\Form;[m
[31m-[m
[31m-use Closure;[m
[31m-use Illuminate\Contracts\View\View;[m
[31m-use Illuminate\View\Component;[m
[31m-[m
[31m-class InputCheckbox extends Component[m
[31m-{[m
[31m-    /**[m
[31m-     * Create a new component instance.[m
[31m-     */[m
[31m-    public function __construct([m
[31m-        public string $attribute,[m
[31m-        public string $label = '',[m
[31m-        public string $helptext = '',[m
[31m-        public bool $isChecked = false,[m
[31m-        public int $bottomSpacing = 2,[m
[31m-    ) {}[m
[31m-[m
[31m-    /**[m
[31m-     * Get the view / contents that represent the component.[m
[31m-     */[m
[31m-    public function render(): View|Closure|string[m
[31m-    {[m
[31m-        $this->label = $this->label ?? $this->attribute;[m
[31m-[m
[31m-        return view('components.form.input-checkbox');[m
[31m-    }[m
[31m-}[m
* Unmerged path app/View/Components/form/InputText.php
[1mdiff --git a/app/View/Components/form/InputText.php b/app/View/Components/form/InputText.php[m
[1mdeleted file mode 100644[m
[1mindex 06e74b2..0000000[m
[1m--- a/app/View/Components/form/InputText.php[m
[1m+++ /dev/null[m
[36m@@ -1,30 +0,0 @@[m
[31m-<?php[m
[31m-[m
[31m-namespace App\View\Components\form;[m
[31m-[m
[31m-use Closure;[m
[31m-use Illuminate\Contracts\View\View;[m
[31m-use Illuminate\View\Component;[m
[31m-[m
[31m-class InputText extends Component[m
[31m-{[m
[31m-    /**[m
[31m-     * Create a new component instance.[m
[31m-     */[m
[31m-    public function __construct([m
[31m-        public string $attribute,[m
[31m-        public string $label = '',[m
[31m-        public bool $autofocus = false,[m
[31m-        public int $bottomSpacing = 2,[m
[31m-    ) {}[m
[31m-[m
[31m-    /**[m
[31m-     * Get the view / contents that represent the component.[m
[31m-     */[m
[31m-    public function render(): View|Closure|string[m
[31m-    {[m
[31m-        $this->label = $this->label ?? $this->attribute;[m
[31m-[m
[31m-        return view('components.form.input-text');[m
[31m-    }[m
[31m-}[m
* Unmerged path public/.htaccess
