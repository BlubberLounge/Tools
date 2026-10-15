@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/minecraft/chainPlanner.js'])
@endpush

@section('content')
<div class="container mx-auto">

    {{-- Configuration for resources/js/minecraft/chainPlanner.js --}}
    <script type="application/json" id="chain-planner-config">
        {!! json_encode([
            'versions' => $versions,
            'endpoints' => [
                'version' => url('/api/v1/minecraft/chains/__VERSION__'),
                'plan' => url('/api/v1/minecraft/chains/plan'),
            ],
            'text' => [
                'loadError' => __('Could not load the version data.'),
                'planError' => __('Could not compute a plan.'),
                'unknown' => __('unknown'),
                'perHour' => __('/h'),
                'graph' => __('Flow graph'),
                'hopperLines' => __('hopper lines'),
                'rawItems' => __('raw items each'),
                'fromFarm' => __('Take from a farm'),
                'rawResource' => __('Take as raw resource'),
                'machines' => [
                    'furnace' => __('furnaces'),
                    'blast_furnace' => __('blast furnaces'),
                    'smoker' => __('smokers'),
                    'villager' => __('villagers'),
                ],
            ],
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <div id="chain-planner" x-cloak>

        {{-- Header --}}
        <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
            <div>
                <h1 class="text-xl font-bold flex items-center gap-2">
                    <i class="fa-solid fa-diagram-project text-primary"></i>
                    {{ __('Farm chain planner') }}
                </h1>
                <p class="text-base-content/60 text-sm max-w-2xl">{{ __('Pick an item and a rate. The planner walks the recipes down to raw resources and shows the items per hour of every step, the farm modules, furnaces, villagers and hopper lines it takes.') }}</p>
            </div>
            <label class="form-control w-full sm:w-56">
                <span class="label-text text-xs mb-1">{{ __('Minecraft version') }}</span>
                <select class="select select-bordered select-sm w-full" x-model="version" data-testid="version-picker">
                    <template x-for="v in versions" :key="v.id">
                        <option :value="v.id" x-text="v.name" :selected="v.id === version"></option>
                    </template>
                </select>
            </label>
        </div>

        {{-- Inputs --}}
        <div class="card bg-base-100 shadow-sm mb-4">
            <div class="card-body p-4 gap-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                    {{-- item picker with search --}}
                    <div class="form-control lg:col-span-2 relative" @click.outside="itemPickerOpen = false">
                        <span class="label-text text-xs mb-1">{{ __('Item') }}</span>
                        <label class="input input-bordered input-sm flex items-center gap-2 w-full">
                            <i class="fa-solid fa-magnifying-glass text-xs opacity-50"></i>
                            <input type="search" class="grow min-w-0" x-model="itemQuery" :placeholder="itemName"
                                   @focus="itemPickerOpen = true" @keydown.escape="itemPickerOpen = false"
                                   @keydown.enter.prevent="filteredItems.length && pickItem(filteredItems[0].id)" data-testid="item-search">
                        </label>
                        <ul class="menu menu-sm bg-base-100 rounded-box shadow-lg border border-base-300 absolute top-full left-0 right-0 z-20 mt-1 max-h-72 overflow-y-auto flex-nowrap"
                            x-show="itemPickerOpen" x-cloak data-testid="item-list">
                            <template x-for="i in filteredItems" :key="i.id">
                                <li>
                                    <button type="button" @click="pickItem(i.id)" :class="i.id === item && 'menu-active'">
                                        <span x-text="i.name"></span>
                                        <span class="text-xs text-base-content/40 ml-auto" x-show="!i.craftable">{{ __('raw') }}</span>
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </div>
                    <label class="form-control">
                        <span class="label-text text-xs mb-1">{{ __('Items per hour') }}</span>
                        <input type="number" min="1" step="any" class="input input-bordered input-sm w-full" x-model.number="rate" data-testid="rate-input">
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs mb-1">{{ __('Fuel') }}</span>
                        <select class="select select-bordered select-sm w-full" x-model="fuel" :class="meta && fuel !== meta.defaults.fuel && 'select-warning'" data-testid="fuel-picker">
                            <template x-for="f in (meta ? meta.fuels : [])" :key="f.item">
                                <option :value="f.item" x-text="`${f.name} (${number(f.smelts)})`" :selected="f.item === fuel"></option>
                            </template>
                        </select>
                    </label>
                    <div class="form-control lg:col-span-2">
                        <span class="label-text text-xs mb-1">{{ __('Recipe choice') }}</span>
                        <div class="join w-full" role="group">
                            <button type="button" class="btn btn-sm join-item grow" :class="mode === 'manual' && 'btn-primary'" @click="mode = 'manual'" data-testid="mode-manual">{{ __('Per node') }}</button>
                            <button type="button" class="btn btn-sm join-item grow" :class="mode === 'auto' && 'btn-primary'" @click="mode = 'auto'" data-testid="mode-auto">{{ __('Automatic (fewest modules)') }}</button>
                        </div>
                    </div>
                </div>

                {{-- Rate overrides --}}
                <details class="collapse collapse-arrow bg-base-200/50 rounded-box">
                    <summary class="collapse-title text-sm min-h-0 py-2 px-3">
                        {{ __('Machine rates') }}
                        <span class="badge badge-warning badge-sm ml-2" x-show="Object.keys(furnaceRates).length || restocksPerDay">{{ __('override') }}</span>
                    </summary>
                    <div class="collapse-content px-3 grid grid-cols-2 lg:grid-cols-4 gap-3">
                        <template x-for="type in ['smelting', 'blasting', 'smoking']" :key="type">
                            <label class="form-control">
                                <span class="label-text text-xs mb-1" x-text="`${text.machines[{smelting: 'furnace', blasting: 'blast_furnace', smoking: 'smoker'}[type]]} (${text.perHour})`"></span>
                                <input type="number" min="1" step="any" class="input input-bordered input-sm w-full"
                                       :class="furnaceRates[type] && 'input-warning'"
                                       :value="furnaceRates[type] || ''" :placeholder="meta ? meta.furnace_rates[type] : ''"
                                       @change="setFurnaceRate(type, $event.target.value)">
                            </label>
                        </template>
                        <label class="form-control">
                            <span class="label-text text-xs mb-1">{{ __('Villager restocks per day') }}</span>
                            <input type="number" min="0.1" step="any" class="input input-bordered input-sm w-full"
                                   :class="restocksPerDay && 'input-warning'"
                                   :value="restocksPerDay || ''" :placeholder="meta ? meta.defaults.restocks_per_day : ''"
                                   @change="restocksPerDay = Number($event.target.value) > 0 ? Number($event.target.value) : null" data-testid="restocks-input">
                        </label>
                    </div>
                </details>

                <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-base-content/60">
                    <span>
                        <span class="loading loading-spinner loading-xs align-middle" x-show="loading"></span>
                        <template x-if="plan">
                            <span>
                                <span class="font-semibold" x-text="plan.totals.modules"></span><span x-show="!plan.totals.modules_complete">+</span> {{ __('farm modules') }}
                                &middot; <span x-text="plan.totals.furnaces"></span> {{ __('furnaces') }}
                                &middot; <span x-text="plan.totals.villagers"></span> {{ __('villagers') }}
                                &middot; <span x-text="plan.totals.hopper_lines"></span> {{ __('hopper lines') }}
                            </span>
                        </template>
                    </span>
                    <button type="button" class="btn btn-ghost btn-xs" x-show="overrideCount > 0" @click="resetOverrides()" data-testid="reset-overrides">
                        <i class="fa-solid fa-rotate-left"></i> {{ __('Reset overrides') }} (<span x-text="overrideCount"></span>)
                    </button>
                </div>
            </div>
        </div>

        <div class="alert alert-error mb-4" x-show="error" x-cloak>
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>
                <p x-text="error"></p>
                <p class="text-xs" x-show="loop.length">{{ __('Loop') }}: <span x-text="loop.join(' → ')"></span></p>
            </div>
            <button type="button" class="btn btn-sm" x-show="overrideCount > 0" @click="resetOverrides()">{{ __('Reset overrides') }}</button>
        </div>

        <div class="alert alert-info alert-soft mb-4 text-sm" x-show="plan && plan.solver" x-cloak>
            <i class="fa-solid fa-calculator"></i>
            <span>
                {{ __('The solver picked the recipes with the fewest farm modules') }}
                (<span x-text="plan && plan.solver ? number(plan.solver.modules) : ''"></span>).
                <span x-show="plan && plan.solver && plan.solver.estimated.length">{{ __('Farms without a published rate count as') }} <span x-text="plan && plan.solver ? number(plan.solver.fallback_module_rate) : ''"></span>{{ __('/h') }}; {{ __('resources no farm makes are only used when nothing else makes the item.') }}</span>
            </span>
        </div>

        {{-- View tabs; the graph is desktop only --}}
        <div role="tablist" class="tabs tabs-box tabs-sm mb-3 w-fit">
            <button type="button" role="tab" class="tab" :class="view === 'list' && 'tab-active'" @click="view = 'list'" data-testid="tab-list"><i class="fa-solid fa-list mr-1"></i>{{ __('Raw resources') }}</button>
            <button type="button" role="tab" class="tab" :class="view === 'tree' && 'tab-active'" @click="view = 'tree'" data-testid="tab-tree"><i class="fa-solid fa-sitemap mr-1"></i>{{ __('Tree') }}</button>
            <button type="button" role="tab" class="tab hidden md:inline-flex" :class="view === 'graph' && 'tab-active'" @click="view = 'graph'" data-testid="tab-graph"><i class="fa-solid fa-diagram-project mr-1"></i>{{ __('Flow graph') }}</button>
        </div>

        <template x-if="plan">
            <div>
                {{-- Raw resources and crafting steps (also shown on phones instead of the graph) --}}
                <div class="space-y-4" x-show="view === 'list' || (view === 'graph' && !desktop)">
                    <div class="card bg-base-100 shadow-sm">
                        <div class="card-body p-0">
                            <h2 class="font-semibold text-sm px-4 pt-3"><i class="fa-solid fa-seedling text-success mr-1"></i>{{ __('Raw resources') }}</h2>
                            <ul class="divide-y divide-base-300" data-testid="source-list">
                                <template x-for="s in plan.sources" :key="s.item">
                                    <li class="px-4 py-3 grid grid-cols-2 md:grid-cols-12 gap-x-3 gap-y-2 items-center">
                                        <div class="col-span-2 md:col-span-3 min-w-0">
                                            <div class="font-medium truncate" x-text="s.name"></div>
                                            <div class="text-xs text-base-content/60 tabular-nums" x-text="rateLabel(s.items_per_hour)"></div>
                                        </div>
                                        <div class="col-span-2 md:col-span-4 min-w-0">
                                            <template x-if="!s.no_farm">
                                                <select class="select select-bordered select-xs w-full" :class="designs[s.item] && 'select-warning'" @change="setDesign(s.item, $event.target.value)">
                                                    <template x-for="d in s.designs" :key="d.id">
                                                        <option :value="d.id" :selected="s.design && d.id === s.design.id" x-text="`${d.name} (${rateLabel(d.per_hour)})`"></option>
                                                    </template>
                                                </select>
                                            </template>
                                            <span class="text-xs text-base-content/50" x-show="s.no_farm">{{ __('No farm design in the catalogue: gather, mine or trade by hand.') }}</span>
                                        </div>
                                        <label class="md:col-span-3 flex items-center gap-1 text-xs">
                                            <span class="text-base-content/60 whitespace-nowrap">{{ __('per module') }}</span>
                                            <input type="number" min="1" step="any" class="input input-bordered input-xs w-full"
                                                   :class="s.module_rate_overridden && 'input-warning'"
                                                   :value="moduleRates[s.item] || ''" :placeholder="s.design && s.design.per_hour !== null ? s.design.per_hour : text.unknown"
                                                   @change="setModuleRate(s.item, $event.target.value)" :data-testid="`module-rate-${s.item}`">
                                        </label>
                                        <div class="md:col-span-2 text-right">
                                            <template x-if="s.modules !== null">
                                                <span class="font-semibold tabular-nums"><span x-text="s.modules"></span> {{ __('modules') }}</span>
                                            </template>
                                            <template x-if="s.modules === null">
                                                <span class="badge badge-warning badge-soft badge-sm" title="{{ __('Enter a module rate to get the module count.') }}">{{ __('rate unknown') }}</span>
                                            </template>
                                            <span class="badge badge-warning badge-xs ml-1" x-show="s.module_rate_overridden || designs[s.item]">{{ __('override') }}</span>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    <div class="card bg-base-100 shadow-sm">
                        <div class="card-body p-0">
                            <h2 class="font-semibold text-sm px-4 pt-3"><i class="fa-solid fa-table-cells text-primary mr-1"></i>{{ __('Crafting steps') }}</h2>
                            <ul class="divide-y divide-base-300" data-testid="step-list">
                                <template x-for="n in craftedNodes" :key="n.item">
                                    <li class="px-4 py-3 grid grid-cols-2 md:grid-cols-12 gap-x-3 gap-y-2 items-center">
                                        <div class="col-span-2 md:col-span-3 min-w-0">
                                            <div class="font-medium truncate" x-text="n.name"></div>
                                            <div class="text-xs text-base-content/60 tabular-nums">
                                                <span x-text="rateLabel(n.items_per_hour)"></span> &middot;
                                                <span x-text="number(n.crafts_per_hour)"></span> {{ __('crafts/h') }}
                                            </div>
                                        </div>
                                        <div class="col-span-2 md:col-span-5 min-w-0">
                                            <select class="select select-bordered select-xs w-full" :class="isChoiceOverridden(n.item) && 'select-warning'"
                                                    :disabled="mode === 'auto'" @change="setChoice(n.item, $event.target.value)" :data-testid="`choice-${n.item}`">
                                                <template x-for="o in n.options" :key="o.id">
                                                    <option :value="o.id" :selected="o.id === n.choice" x-text="optionLabel(o)"></option>
                                                </template>
                                            </select>
                                        </div>
                                        <div class="md:col-span-2 text-xs tabular-nums" x-text="machineLabel(n.machine)"></div>
                                        <div class="md:col-span-2 text-xs tabular-nums text-right">
                                            <span x-show="n.fuel" x-text="n.fuel ? `${number(n.fuel.per_hour)} ${n.fuel.name}${text.perHour}` : ''"></span>
                                            <span class="badge badge-warning badge-xs ml-1" x-show="isChoiceOverridden(n.item) || (n.machine && n.machine.overridden)">{{ __('override') }}</span>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                            <p class="p-4 text-sm text-base-content/50" x-show="craftedNodes.length === 0">{{ __('The item is a raw resource.') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Indented tree --}}
                <div class="card bg-base-100 shadow-sm" x-show="view === 'tree'">
                    <ul class="py-2 text-sm" data-testid="tree">
                        <template x-for="row in treeRows" :key="row.key">
                            <li class="flex items-center gap-2 py-1 pr-4 hover:bg-base-200" :style="`padding-left: ${1 + row.depth * 1.25}rem`">
                                <i class="fa-solid text-xs w-4 text-center" :class="[kindIcon(row), row.kind === 'source' ? 'text-success' : (row.loop ? 'text-warning' : 'text-primary')]"></i>
                                <span class="truncate" x-text="row.name"></span>
                                <span class="badge badge-ghost badge-xs" x-show="row.loop">{{ __('loop') }}</span>
                                <span class="ml-auto tabular-nums font-semibold" x-text="rateLabel(row.items_per_hour)"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                {{-- Flow graph (desktop) --}}
                <div class="card bg-base-100 shadow-sm hidden md:block" x-show="view === 'graph' && desktop">
                    <div class="overflow-auto p-2" x-html="graphSvg" data-testid="graph"></div>
                    <p class="text-xs text-base-content/50 px-4 pb-3">{{ __('Edges show items per hour (×n = parallel hopper lines at 9000 items per hour each); dashed edges loop back, yellow edges carry fuel.') }}</p>
                </div>
            </div>
        </template>

        {{-- Data source --}}
        <div class="text-xs text-base-content/50 px-1 mt-3 space-y-1" x-show="meta">
            <p x-text="meta && meta.notes"></p>
            <p>
                {{ __('Data') }}: <span x-text="meta && meta.name"></span>, {{ __('updated') }} <span x-text="meta && meta.updated"></span> &middot;
                <template x-for="(source, index) in (meta ? meta.sources : [])" :key="source.url">
                    <span><a class="link" :href="source.url" target="_blank" rel="noopener" x-text="source.title"></a><span x-show="index < meta.sources.length - 1">, </span></span>
                </template>
                @if (auth()->user()?->hasPermission('view.minecraft.farm.catalogue'))
                    &middot; <a class="link" href="{{ route('minecraft.farm-catalogue') }}">{{ __('Farm module catalogue') }}</a>
                @endif
            </p>
        </div>
    </div>
</div>
@endsection
