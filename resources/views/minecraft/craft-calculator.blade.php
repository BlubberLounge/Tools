@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/minecraft/craftCalculator.js'])
@endpush

@section('content')
<div class="container mx-auto">

    {{-- Configuration for resources/js/minecraft/craftCalculator.js --}}
    <script type="application/json" id="craft-calculator-config">
        {!! json_encode([
            'items' => $items,
            'recipes' => $recipes,
            'choices' => $choices,
            'fuels' => $fuels,
            'text' => [
                'gather' => __('Gather / farm'),
                'types' => [
                    'smelting' => __('furnace'),
                    'blasting' => __('blast furnace'),
                    'smoking' => __('smoker'),
                ],
            ],
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES) !!}
    </script>

    <div id="craft-calculator" x-cloak>

        {{-- Header --}}
        <div class="mb-4">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-hammer text-primary"></i>
                {{ __('Crafting calculator') }}
            </h1>
            <p class="text-base-content/60 text-sm max-w-3xl">{{ __('Crafts and raw materials for any amount of an item. Switch any step to another recipe and compare the ways to make it.') }} ({{ $versionName }}, {{ __('recipes of the farm chain planner') }})</p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

            {{-- Input and comparison --}}
            <div class="flex flex-col gap-4">
                <div class="card bg-base-100 shadow-sm" data-testid="craft-input">
                    <div class="card-body p-4 gap-3">
                        <h2 class="card-title text-sm"><i class="fa-solid fa-pen text-primary"></i>{{ __('Input') }}</h2>
                        <label class="form-control">
                            <span class="label-text text-xs mb-1">{{ __('Item') }}</span>
                            <select class="select select-bordered select-sm w-full" x-model="target">
                                <template x-for="i in items" :key="i.id">
                                    <option :value="i.id" x-text="i.name" :selected="target === i.id"></option>
                                </template>
                            </select>
                        </label>
                        <label class="form-control">
                            <span class="label-text text-xs mb-1">{{ __('Amount') }}</span>
                            <input type="number" min="1" class="input input-bordered input-sm w-full" x-model="amount">
                        </label>
                        <div class="flex flex-wrap gap-1">
                            <template x-for="n in [1, 16, 64, 1728]" :key="n">
                                <button type="button" class="btn btn-xs" @click="amount = n" x-text="n"></button>
                            </template>
                            <button type="button" class="btn btn-ghost btn-xs" @click="reset()"><i class="fa-solid fa-rotate-left"></i>{{ __('Default ways') }}</button>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-sm" data-testid="craft-ways">
                    <div class="card-body p-4 gap-2">
                        <h2 class="card-title text-sm"><i class="fa-solid fa-scale-balanced text-primary"></i>{{ __('Ways to make it') }}</h2>
                        <template x-for="w in ways" :key="w.way">
                            <button type="button" class="rounded-box bg-base-200 px-3 py-2 text-left text-sm hover:bg-base-300" :class="choice(target) === w.way && 'ring-2 ring-primary'" @click="choose(target, w.way)">
                                <span class="block font-semibold" x-text="recipeLabel(w.way)"></span>
                                <span class="block text-xs text-base-content/70" x-text="rawText(w.raw)"></span>
                            </button>
                        </template>
                        <p class="text-xs text-base-content/50">{{ __('Fewest raw items first, with the ways chosen for the other steps.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Steps --}}
            <div class="card bg-base-100 shadow-sm xl:col-span-2" data-testid="craft-steps">
                <div class="card-body p-4 gap-3">
                    <h2 class="card-title text-sm"><i class="fa-solid fa-list-check text-primary"></i>{{ __('Raw materials') }}</h2>

                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>{{ __('Item') }}</th><th class="text-right">{{ __('Count') }}</th><th>{{ __('Way') }}</th></tr>
                            </thead>
                            <tbody>
                                <template x-for="r in result.raw" :key="r.item">
                                    <tr>
                                        <td x-text="name(r.item)"></td>
                                        <td class="text-right font-mono font-semibold" x-text="format(r.count, 0)"></td>
                                        <td>
                                            <select class="select select-bordered select-xs w-full max-w-xs" x-show="hasOptions(r.item)" @change="choose(r.item, $event.target.value)">
                                                <template x-for="o in options(r.item)" :key="o">
                                                    <option :value="o" x-text="recipeLabel(o)" :selected="choice(r.item) === o"></option>
                                                </template>
                                            </select>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-sm" x-show="result.cookOps > 0">
                        <i class="fa-solid fa-fire text-warning"></i>
                        <span x-text="format(result.cookOps, 0)"></span> {{ __('furnace operations') }},
                        <select class="select select-bordered select-xs" x-model="fuel">
                            <template x-for="f in fuels" :key="f.item">
                                <option :value="f.item" x-text="f.name" :selected="fuel === f.item"></option>
                            </template>
                        </select>:
                        <span class="font-semibold" x-text="format(fuelNeeded, 0)"></span>
                    </p>
                    <p class="text-sm text-warning" x-show="result.forced.length > 0">
                        <i class="fa-solid fa-rotate"></i>
                        {{ __('These ways loop back to themselves, so the item is gathered instead:') }}
                        <span x-text="result.forced.map(i => name(i)).join(', ')"></span>
                    </p>

                    <h2 class="card-title text-sm mt-2"><i class="fa-solid fa-hammer text-primary"></i>{{ __('Crafting steps') }}</h2>
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Item') }}</th>
                                    <th>{{ __('Way') }}</th>
                                    <th class="text-right">{{ __('Crafts') }}</th>
                                    <th class="text-right">{{ __('Made') }}</th>
                                    <th class="text-right">{{ __('Left over') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="s in result.steps" :key="s.item">
                                    <tr>
                                        <td x-text="name(s.item)"></td>
                                        <td>
                                            <select class="select select-bordered select-xs w-full max-w-xs" @change="choose(s.item, $event.target.value)">
                                                <template x-for="o in options(s.item)" :key="o">
                                                    <option :value="o" x-text="recipeLabel(o)" :selected="choice(s.item) === o"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="text-right font-mono" x-text="format(s.crafts, 0)"></td>
                                        <td class="text-right font-mono" x-text="format(s.made, 0)"></td>
                                        <td class="text-right font-mono" :class="s.leftover > 0 && 'text-warning'" x-text="format(s.leftover, 0)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-xs text-base-content/50">{{ __('Steps are in crafting order: start at the top. Only the recipes the farm chain planner needs are included; where any planks or logs work, oak stands for all of them.') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
