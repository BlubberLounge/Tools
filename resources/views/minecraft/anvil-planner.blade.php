@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/minecraft/anvilPlanner.js'])
@endpush

@push('styles')
    @vite(['resources/css/minecraft-anvil.css'])
@endpush

@section('content')
<div class="container mx-auto">

    {{-- Configuration for resources/js/minecraft/anvilPlanner.js --}}
    <script type="application/json" id="anvil-planner-config">
        {!! json_encode([
            'versions' => $versions,
            'endpoints' => [
                'version' => url('/api/v1/minecraft/versions/__VERSION__'),
                'plan' => url('/api/v1/minecraft/anvil/plan'),
            ],
            'text' => [
                'loadError' => __('Could not load the version data.'),
                'planError' => __('Could not compute a plan.'),
                'excludedBy' => __('excluded by'),
                'book' => __('Enchanted Book'),
                'resultOfStep' => __('result of step'),
                'step' => __('Step'),
                'of' => __('of'),
                'cost' => __('Enchantment Cost'),
                'tooExpensive' => __('Too Expensive!'),
                'chooseItem' => __('Choose an item'),
                'leftSlot' => __('Left slot'),
                'rightSlot' => __('Right slot'),
                'result' => __('Result'),
            ],
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <div id="anvil-planner" x-cloak>

        {{-- Header --}}
        <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
            <div>
                <h1 class="text-xl font-bold flex items-center gap-2">
                    <i class="fa-solid fa-hammer text-primary"></i>
                    {{ __('Anvil enchantment planner') }}
                </h1>
                <p class="text-base-content/60 text-sm max-w-2xl">{{ __('Pick a version, an item and the enchantments you want. The planner finds the cheapest anvil order that keeps every step at or below the level cap.') }}</p>
            </div>
            <label class="form-control w-full sm:w-56">
                <span class="label-text text-xs mb-1">{{ __('Minecraft version') }}</span>
                <select class="select select-bordered select-sm w-full" x-model="version" @change="loadVersion()" data-testid="version-picker">
                    <template x-for="v in versions" :key="v.id">
                        <option :value="v.id" x-text="v.name"></option>
                    </template>
                </select>
            </label>
        </div>

        <div class="alert alert-error mb-4" x-show="error" x-cloak>
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span x-text="error"></span>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-5 gap-4">

            {{-- Left: item and enchantments --}}
            <div class="xl:col-span-2 space-y-4">

                {{-- Item picker --}}
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body p-4 gap-3">
                        <h2 class="card-title text-sm"><i class="fa-solid fa-box-open text-primary"></i>{{ __('Item') }}</h2>

                        <button type="button" class="btn btn-sm justify-start" :class="item ? 'btn-primary btn-outline' : 'btn-ghost border-base-300'" @click="itemPickerOpen = !itemPickerOpen">
                            <span class="mc-item-icon w-5 h-5" x-show="item" x-html="item ? iconFor(item) : ''"></span>
                            <span class="truncate" x-text="item ? item.name : text.chooseItem"></span>
                            <i class="fa-solid fa-chevron-down ml-auto text-xs" :class="itemPickerOpen && 'rotate-180'"></i>
                        </button>

                        <div x-show="itemPickerOpen" x-collapse>
                            <label class="input input-bordered input-sm flex items-center gap-2 w-full mb-2">
                                <i class="fa-solid fa-magnifying-glass text-xs opacity-50"></i>
                                <input type="search" class="grow" x-model="itemQuery" placeholder="{{ __('Search items') }}" data-testid="item-search">
                            </label>
                            <div class="max-h-72 overflow-y-auto custom-scrollbar rounded-box border border-base-300">
                                <template x-for="group in itemGroups" :key="group.category">
                                    <div>
                                        <div class="sticky top-0 z-10 bg-base-200 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-base-content/60" x-text="group.category"></div>
                                        <ul class="menu menu-sm w-full p-1">
                                            <template x-for="it in group.items" :key="it.id">
                                                <li>
                                                    <a :class="item && item.id === it.id && 'menu-active'" @click.prevent="selectItem(it)">
                                                        <span class="mc-item-icon w-4 h-4" x-html="iconFor(it)"></span>
                                                        <span x-text="it.name"></span>
                                                    </a>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </template>
                                <p class="p-3 text-sm text-base-content/50" x-show="itemGroups.length === 0">{{ __('No items found.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Enchantments --}}
                <div class="card bg-base-100 shadow-sm" x-show="item">
                    <div class="card-body p-4 gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="card-title text-sm"><i class="fa-solid fa-wand-sparkles text-primary"></i>{{ __('Enchantments') }}</h2>
                            <button type="button" class="btn btn-ghost btn-xs" @click="clearSelection()">{{ __('Reset') }}</button>
                        </div>
                        <p class="text-xs text-base-content/60">{{ __('Add: buy a book at that level. Present: the item already has it.') }}</p>

                        <ul class="divide-y divide-base-300">
                            <template x-for="ench in itemEnchantments" :key="ench.id">
                                <li class="py-2 flex flex-wrap items-center gap-2" :class="blockedBy(ench) && 'opacity-40'">
                                    <div class="min-w-0 grow basis-40">
                                        <div class="font-medium text-sm" :class="ench.curse && 'text-error'" x-text="ench.name"></div>
                                        <div class="text-xs text-base-content/50" x-show="blockedBy(ench)" x-text="text.excludedBy + ' ' + blockedBy(ench)"></div>
                                    </div>
                                    <select class="select select-bordered select-xs w-16" x-show="ench.max_level > 1" x-model.number="levels[ench.id]" :disabled="!!blockedBy(ench)" :aria-label="ench.name">
                                        <template x-for="lvl in ench.max_level" :key="lvl">
                                            <option :value="lvl" x-text="roman(lvl)" :selected="lvl === levels[ench.id]"></option>
                                        </template>
                                    </select>
                                    <div class="join">
                                        <button type="button" class="join-item btn btn-xs" :class="modes[ench.id] === 'want' ? 'btn-primary' : 'btn-ghost border-base-300'" :disabled="!!blockedBy(ench)" @click="setMode(ench, 'want')" :data-testid="'want-' + ench.id">{{ __('Add') }}</button>
                                        <button type="button" class="join-item btn btn-xs" :class="modes[ench.id] === 'present' ? 'btn-secondary' : 'btn-ghost border-base-300'" :disabled="!!blockedBy(ench)" @click="setMode(ench, 'present')" title="{{ __('Already on the item') }}">{{ __('Present') }}</button>
                                    </div>
                                </li>
                            </template>
                        </ul>

                        <div class="flex flex-wrap items-end gap-3 pt-2">
                            <label class="form-control w-36">
                                <span class="label-text text-xs mb-1">{{ __('Prior anvil uses') }}</span>
                                <input type="number" min="0" max="10" class="input input-bordered input-sm" x-model.number="priorWork">
                            </label>
                            <button type="button" class="btn btn-primary btn-sm ml-auto" :disabled="!canPlan || loading" @click="plan()" data-testid="plan-button">
                                <span class="loading loading-spinner loading-xs" x-show="loading"></span>
                                <i class="fa-solid fa-calculator" x-show="!loading"></i>
                                {{ __('Plan') }}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Data source --}}
                <div class="text-xs text-base-content/50 px-1" x-show="data">
                    {{ __('Data') }}: <span x-text="data && data.name"></span> &middot;
                    <template x-for="(source, index) in (data ? data.sources : [])" :key="source.url">
                        <span><a class="link" :href="source.url" target="_blank" rel="noopener" x-text="source.title"></a><span x-show="index < data.sources.length - 1">, </span></span>
                    </template>
                    (Minecraft Wiki, <span x-text="data && data.sources[0].retrieved"></span>)
                </div>
            </div>

            {{-- Right: plan --}}
            <div class="xl:col-span-3 space-y-4">

                <div class="card bg-base-100 shadow-sm" x-show="!result">
                    <div class="card-body items-center text-center text-base-content/50 py-12">
                        <i class="fa-solid fa-hammer text-3xl mb-2"></i>
                        <p class="text-sm">{{ __('Choose an item and its enchantments, then press Plan.') }}</p>
                    </div>
                </div>

                <template x-if="result">
                    <div class="space-y-4">

                        <div class="alert alert-error" x-show="!result.possible">
                            <i class="fa-solid fa-ban"></i>
                            <div>
                                <div class="font-semibold">{{ __('No order stays within the level cap.') }}</div>
                                <div class="text-sm">
                                    {{ __('The cheapest order fails at step') }} <span x-text="result.failing_step"></span>
                                    (<span x-text="result.failing_step ? result.steps[result.failing_step - 1].cost : ''"></span> &gt; <span x-text="result.level_cap"></span> {{ __('levels') }}).
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info" x-show="result.possible && result.steps.length === 0">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>{{ __('Nothing to apply: the item already has everything you selected.') }}</span>
                        </div>

                        {{-- Summary --}}
                        <div class="stats stats-vertical sm:stats-horizontal shadow-sm w-full bg-base-100">
                            <div class="stat py-3">
                                <div class="stat-title">{{ __('Total levels') }}</div>
                                <div class="stat-value text-2xl" x-text="result.total_levels"></div>
                                <div class="stat-desc">{{ __('max per step') }} <span x-text="result.max_step_cost"></span> / <span x-text="result.level_cap"></span></div>
                            </div>
                            <div class="stat py-3">
                                <div class="stat-title">{{ __('XP points') }}</div>
                                <div class="stat-value text-2xl" x-text="formatNumber(result.total_xp_points)"></div>
                                <div class="stat-desc">{{ __('each step counted from level 0') }}</div>
                            </div>
                            <div class="stat py-3">
                                <div class="stat-title">{{ __('Books') }}</div>
                                <div class="stat-value text-2xl" x-text="result.books"></div>
                                <div class="stat-desc"><span x-text="result.steps.length"></span> {{ __('anvil steps') }}</div>
                            </div>
                        </div>

                        {{-- XP sources --}}
                        <div class="card bg-base-100 shadow-sm" x-show="result.total_xp_points > 0">
                            <div class="card-body p-4 gap-2">
                                <h2 class="card-title text-sm"><i class="fa-solid fa-star text-primary"></i>{{ __('Where the XP comes from') }}</h2>
                                <div class="flex flex-wrap items-center gap-2 text-sm">
                                    <select class="select select-bordered select-sm w-48" x-model="xpSource" aria-label="{{ __('XP source') }}">
                                        <template x-for="mob in result.xp_sources.mobs" :key="mob.id">
                                            <option :value="mob.id" x-text="mob.name + ' (' + mob.xp + ' XP)'" :selected="mob.id === xpSource"></option>
                                        </template>
                                    </select>
                                    <span>&asymp; <strong x-text="formatNumber(selectedMob ? selectedMob.count : 0)"></strong> {{ __('kills') }}</span>
                                </div>
                                <template x-for="source in result.xp_sources.smelting" :key="source.id">
                                    <p class="text-sm">
                                        {{ __('or') }} <strong x-text="formatNumber(source.count)"></strong> &times; <span x-text="source.name"></span>
                                        <span class="text-base-content/50">(<span x-text="source.xp"></span> XP {{ __('each') }})</span>
                                    </p>
                                </template>
                            </div>
                        </div>

                        {{-- Anvil view + step list --}}
                        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4" x-show="result.steps.length > 0">
                            <div class="lg:col-span-3 space-y-2">
                                <div class="mc-anvil" :class="currentStep && currentStep.too_expensive && 'mc-anvil--too-expensive'" data-testid="anvil-view">
                                    <div class="mc-anvil__panel"></div>
                                    <div class="mc-anvil__title" x-text="currentStep ? text.step + ' ' + currentStep.number + ' ' + text.of + ' ' + result.steps.length : ''"></div>
                                    <div class="mc-anvil__hammer" x-html="anvilGlyph"></div>
                                    <div class="mc-anvil__name" x-text="currentStep ? currentStep.left.name : ''"></div>
                                    <div class="mc-anvil__slot mc-anvil__slot--left" :class="currentStep && currentStep.left.enchantments.length && 'mc-anvil__slot--glint'" x-html="currentStep ? slotIcon(currentStep.left) : ''" :title="currentStep ? slotTitle(currentStep.left) : ''"></div>
                                    <div class="mc-anvil__plus"></div>
                                    <div class="mc-anvil__slot mc-anvil__slot--right" :class="currentStep && 'mc-anvil__slot--glint'" x-html="currentStep ? slotIcon(currentStep.right) : ''" :title="currentStep ? slotTitle(currentStep.right) : ''"></div>
                                    <div class="mc-anvil__arrow" :class="currentStep && currentStep.too_expensive && 'mc-anvil__arrow--blocked'"></div>
                                    <div class="mc-anvil__slot mc-anvil__slot--result mc-anvil__slot--glint" x-html="currentStep && !currentStep.too_expensive ? slotIcon(currentStep.result) : ''" :title="currentStep ? slotTitle(currentStep.result) : ''"></div>
                                    <div class="mc-anvil__cost" x-text="currentStep ? (currentStep.too_expensive ? text.tooExpensive : text.cost + ': ' + currentStep.cost) : ''"></div>
                                </div>

                                <div class="flex items-center justify-between gap-2">
                                    <button type="button" class="btn btn-sm" :disabled="stepIndex === 0" @click="stepIndex--"><i class="fa-solid fa-chevron-left"></i>{{ __('Previous') }}</button>
                                    <span class="text-sm text-base-content/60" x-text="(stepIndex + 1) + ' / ' + result.steps.length"></span>
                                    <button type="button" class="btn btn-sm" :disabled="stepIndex >= result.steps.length - 1" @click="stepIndex++">{{ __('Next') }}<i class="fa-solid fa-chevron-right"></i></button>
                                </div>

                                {{-- Step details (readable on phones, where the slot tooltips do not work) --}}
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs" x-show="currentStep">
                                    <template x-for="part in (currentStep ? [[text.leftSlot, currentStep.left], [text.rightSlot, currentStep.right], [text.result, currentStep.result]] : [])" :key="part[0]">
                                        <div class="rounded-box border border-base-300 p-2">
                                            <div class="font-semibold text-base-content/70" x-text="part[0]"></div>
                                            <div x-text="slotName(part[1])"></div>
                                            <ul class="text-base-content/60">
                                                <template x-for="e in part[1].enchantments" :key="e.id">
                                                    <li x-text="enchantmentLabel(e)" :class="e.curse && 'text-error'"></li>
                                                </template>
                                            </ul>
                                            <div class="text-base-content/40" x-show="part[1].prior_work > 0">{{ __('Prior work penalty') }}: <span x-text="part[1].penalty"></span></div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="lg:col-span-2">
                                <ol class="space-y-1" data-testid="step-list">
                                    <template x-for="(step, index) in result.steps" :key="step.number">
                                        <li>
                                            <button type="button" class="w-full text-left rounded-box border px-3 py-2 text-sm transition" :class="index === stepIndex ? 'border-primary bg-primary/10' : 'border-base-300 hover:bg-base-200'" @click="stepIndex = index">
                                                <div class="flex items-center justify-between gap-2">
                                                    <span class="font-semibold" x-text="text.step + ' ' + step.number"></span>
                                                    <span class="badge badge-sm" :class="step.too_expensive ? 'badge-error' : 'badge-success'" x-text="step.cost + ' lvl'"></span>
                                                </div>
                                                <div class="text-xs text-base-content/60">
                                                    <span x-text="slotShort(step.left)"></span>
                                                    <span class="opacity-60">+</span>
                                                    <span x-text="slotShort(step.right)"></span>
                                                </div>
                                                <div class="text-xs text-base-content/40"><span x-text="formatNumber(step.xp_points)"></span> XP</div>
                                            </button>
                                        </li>
                                    </template>
                                </ol>
                                <div class="mt-2 text-sm flex justify-between px-1">
                                    <span>{{ __('Total') }}</span>
                                    <span><strong x-text="result.total_levels"></strong> {{ __('levels') }}, <strong x-text="result.books"></strong> {{ __('books') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
