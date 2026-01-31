@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/dart/dartInfo.js'])
@endpush

@push('styles')
    @vite(['resources/sass/dartboardResult.scss', 'resources/sass/dartboard.scss'])
    <style>
        .info-card {
            background-color: #ffffff;
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            margin-bottom: 1.5rem;
            break-inside: avoid;
        }
        .dark .info-card {
            background-color: #1e293b;
            border-color: #334155;
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.3);
        }
        .info-card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .dark .info-card-header {
            border-bottom-color: #334155;
        }
        .info-card-header i {
            color: #f97316;
            font-size: 1.25rem;
        }
        .info-card-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.125rem;
            color: #1e293b;
        }
        .dark .info-card-header h3 {
            color: #e2e8f0;
        }
        .info-card p {
            color: #64748b;
            font-size: 0.875rem;
            line-height: 1.6;
        }
        .dark .info-card p {
            color: #94a3b8;
        }
        .dartboard-wrapper {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }
        .dartboard-d3 {
            display: flex;
            justify-content: center;
            width: 100%;
            max-width: 360px;
        }
        .dartboard-tooltip {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            pointer-events: none;
            z-index: 10;
            background: rgba(255, 255, 255, 0.95);
            padding: 0.75rem 1.25rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        .dark .dartboard-tooltip {
            background: rgba(30, 41, 59, 0.95);
        }
        .dartboard-tooltip.visible {
            opacity: 1;
        }
        .dartboard-tooltip .tooltip-value {
            display: block;
            font-size: 2rem;
            font-weight: 700;
            color: #f97316;
            line-height: 1;
        }
        .dartboard-tooltip .tooltip-label {
            display: block;
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 0.25rem;
        }
        .dark .dartboard-tooltip .tooltip-label {
            color: #94a3b8;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .stat-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        .stat-item {
            text-align: center;
            padding: 1rem;
            background: #f1f5f9;
            border-radius: 0.75rem;
        }
        .dark .stat-item {
            background: #334155;
        }
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #f97316;
        }
        .stat-label {
            font-size: 0.75rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .dark .stat-label {
            color: #94a3b8;
        }
        .graph-container {
            min-height: 300px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .rotate-hint {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 0.75rem;
            color: #92400e;
            margin-bottom: 1.5rem;
        }
        .dark .rotate-hint {
            background: #78350f;
            border-color: #b45309;
            color: #fef3c7;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #475569;
        }
        .dark .legend-item {
            color: #94a3b8;
        }
        .legend-color {
            width: 1rem;
            height: 1rem;
            border-radius: 0.25rem;
        }

        /* Dartboard Heatmap Wrapper */
        .dartboard-heatmap-wrapper {
            position: relative;
        }
        .heatmap-canvas-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 340px;
            height: 340px;
            pointer-events: none;
            border-radius: 50%;
            overflow: hidden;
        }
        .throw-overlay-svg {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 340px;
            height: 340px;
            pointer-events: none;
        }

        /* Overlay Legend */
        .overlay-legend {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }
        .dark .overlay-legend {
            border-top-color: #334155;
        }
        .overlay-legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #475569;
        }
        .dark .overlay-legend-item {
            color: #94a3b8;
        }
        .legend-dot {
            width: 0.75rem;
            height: 0.75rem;
            border-radius: 50%;
        }
        .legend-line {
            width: 1.5rem;
            height: 0.25rem;
            border-radius: 0.125rem;
        }

        /* Heatmap Legend */
        .heatmap-legend {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
            font-size: 0.75rem;
            color: #64748b;
        }
        .dark .heatmap-legend {
            border-top-color: #334155;
            color: #94a3b8;
        }
        .legend-gradient {
            width: 8rem;
            height: 0.75rem;
            background: linear-gradient(to right, #050aac, #6a89f7, #bebebe, #dcaa84, #e6915a, #b20a1c);
            border-radius: 0.25rem;
        }
        .legend-low, .legend-high {
            font-weight: 600;
        }

        /* Strategic Legend */
        .strategic-legend {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }
        .dark .strategic-legend {
            border-top-color: #334155;
        }
        .legend-scale {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            font-size: 0.75rem;
            color: #64748b;
        }
        .dark .legend-scale {
            color: #94a3b8;
        }
        .scale-gradient {
            width: 6rem;
            height: 0.5rem;
            background: linear-gradient(to right, #22c55e, #f97316);
            border-radius: 9999px;
        }

        /* Field Order Pills */
        .field-pill {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
            border-radius: 0.5rem;
            cursor: grab;
            user-select: none;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .field-pill:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.4);
        }
        .field-pill:active {
            cursor: grabbing;
        }
        .field-pill.dragging {
            opacity: 0.5;
            transform: scale(1.1);
        }
        .field-pill.drag-over {
            transform: scale(0.9);
        }
        #fieldOrderPills.drag-active {
            background: rgba(249, 115, 22, 0.1);
            border-color: #f97316;
        }
    </style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">

    <!-- Header -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-orange-500/10 mb-4">
            <i class="fa-solid fa-circle-info text-3xl text-orange-500"></i>
        </div>
        <h1 class="text-3xl font-bold text-slate-800 dark:text-slate-100 mb-2">{{ __('Dart Information') }}</h1>
        <p class="text-slate-500 dark:text-slate-400 max-w-2xl mx-auto">
            {{ __('Explore statistics, field values, and heatmaps to improve your dart game strategy.') }}
        </p>
    </div>

    <!-- Field Order Configuration -->
    <div class="info-card mb-6">
        <div class="info-card-header">
            <i class="fa-solid fa-shuffle"></i>
            <h3>{{ __('Dartboard Field Order') }}</h3>
        </div>
        <p class="mb-4">{{ __('Customize the dartboard field arrangement to see how different layouts affect scoring patterns.') }}</p>
        <div>
            <label class="block text-sm font-medium text-slate-600 dark:text-slate-400 mb-2">
                {{ __('Field Order (clockwise from top)') }}
            </label>
            <div id="fieldOrderPills" class="flex flex-wrap gap-2 p-3 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-800/50 min-h-[60px] mb-3">
                @foreach([20, 1, 18, 4, 13, 6, 10, 15, 2, 17, 3, 19, 7, 16, 8, 11, 14, 9, 12, 5] as $number)
                    <div class="field-pill" draggable="true" data-value="{{ $number }}">
                        {{ $number }}
                    </div>
                @endforeach
            </div>
            <div class="flex gap-3">
                <button
                    type="button"
                    id="resetFieldOrderBtn"
                    class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 rounded-lg transition-colors"
                >
                    <i class="fa-solid fa-rotate-left mr-1"></i>
                    {{ __('Reset') }}
                </button>
                <button
                    type="button"
                    id="applyFieldOrderBtn"
                    class="px-4 py-2 text-sm font-medium text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                >
                    <i class="fa-solid fa-check mr-1"></i>
                    {{ __('Apply') }}
                </button>
            </div>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-500">{{ __('Drag and drop to reorder fields. Click Apply to update all dartboards.') }}</p>
        </div>
    </div>

    @mobile
        <div class="rotate-hint">
            <i class="fa-solid fa-mobile-screen-button fa-2x fa-rotate-90"></i>
            <span>{{ __('Rotate your device for better graph visibility') }}</span>
        </div>
    @endmobile

    <!-- Quick Stats -->
    <div class="info-card">
        <div class="info-card-header">
            <i class="fa-solid fa-chart-simple"></i>
            <h3>{{ __('Quick Facts') }}</h3>
        </div>
        <div class="stat-grid">
            <div class="stat-item">
                <div class="stat-value">180</div>
                <div class="stat-label">{{ __('Max Score') }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">60</div>
                <div class="stat-label">{{ __('Triple 20') }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">50</div>
                <div class="stat-label">{{ __('Bullseye') }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">170</div>
                <div class="stat-label">{{ __('Max Checkout') }}</div>
            </div>
        </div>
    </div>

    <!-- Masonry Layout -->
    <div class="columns-1 lg:columns-2 gap-6">

        <!-- Interactive Dartboard -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fa-solid fa-bullseye"></i>
                <h3>{{ __('Interactive Dartboard') }}</h3>
            </div>
            <p class="mb-4">{{ __('Hover over segments to see their values. Click to select a target.') }}</p>
            <div class="dartboard-wrapper">
                <div id="interactiveDartboard" class="dartboard-d3"></div>
                <div id="dartboardTooltip" class="dartboard-tooltip">
                    <span class="tooltip-value">-</span>
                    <span class="tooltip-label">{{ __('points') }}</span>
                </div>
            </div>
            <div class="flex flex-wrap justify-center gap-4 mt-4">
                <div class="legend-item">
                    <div class="legend-color" style="background: #E3292E;"></div>
                    <span>{{ __('Double/Triple (Red)') }}</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #32a63b;"></div>
                    <span>{{ __('Double/Triple (Green)') }}</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #1a1a1a;"></div>
                    <span>{{ __('Single (Black)') }}</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: ivory;"></div>
                    <span>{{ __('Single (White)') }}</span>
                </div>
            </div>
        </div>

        <!-- Throw Distribution Graph -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fa-solid fa-chart-pie"></i>
                <h3>{{ __('Score Distribution') }}</h3>
            </div>
            <p class="mb-4">{{ __('Visual representation of score distribution across all dart throws.') }}</p>
            <div class="graph-container">
                <div id="dartboardDataGraph" style="width: 100%; max-width: 500px; aspect-ratio: 1;"></div>
            </div>
        </div>

        <!-- Throw Heatmap with Dartboard -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fa-solid fa-crosshairs"></i>
                <h3>{{ __('Throw Distribution Heatmap') }}</h3>
            </div>
            <p class="mb-4">{{ __('Heatmap shows throw concentration. Green line indicates optimal aim path based on skill level.') }}</p>
            <div class="dartboard-wrapper dartboard-heatmap-wrapper" id="throwHeatmapWrapper">
                <div id="throwHeatmapDartboard" class="dartboard-d3"></div>
                <div id="throwHeatmapContainer" class="heatmap-canvas-container"></div>
                <svg id="throwOverlaySvg" class="throw-overlay-svg"></svg>
            </div>
            <div class="overlay-legend">
                <div class="overlay-legend-item">
                    <span class="legend-dot" style="background: #ff7f0e;"></span>
                    <span>{{ __('Throw density') }}</span>
                </div>
                <div class="overlay-legend-item">
                    <span class="legend-line" style="background: #00ff00;"></span>
                    <span>{{ __('Optimal aim path') }}</span>
                </div>
            </div>
        </div>

        <!-- Expected Value Analysis -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fa-solid fa-calculator"></i>
                <h3>{{ __('Expected Value Analysis') }}</h3>
            </div>
            <p class="mb-4">{{ __('Statistical analysis of expected values for different target areas.') }}</p>
            <div class="graph-container">
                <div id="expectationDataGraph" style="width: 100%;"></div>
            </div>
        </div>

        <!-- Performance Trends -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fa-solid fa-chart-line"></i>
                <h3>{{ __('Performance Trends') }}</h3>
            </div>
            <p class="mb-4">{{ __('Track scoring patterns and identify areas for improvement.') }}</p>
            <div class="graph-container">
                <div id="graph01" style="width: 100%;"></div>
            </div>
        </div>

        <!-- Field Values -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fa-solid fa-table-cells"></i>
                <h3>{{ __('Dartboard Field Values') }}</h3>
            </div>
            <p class="mb-4">{{ __('Hover over segments to see values. Outer ring = double, inner thin ring = triple.') }}</p>
            <div class="dartboard-wrapper">
                <div id="fieldValuesDartboard" class="dartboard-d3"></div>
                <div id="fieldValuesTooltip" class="dartboard-tooltip">
                    <span class="tooltip-value">-</span>
                    <span class="tooltip-label">{{ __('points') }}</span>
                </div>
            </div>
        </div>

        <!-- Heatmap -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fa-solid fa-fire"></i>
                <h3>{{ __('Popularity Heatmap') }}</h3>
            </div>
            <p class="mb-4">{{ __('Brighter segments are targeted more often. Hover to see exact percentages.') }}</p>
            <div class="dartboard-wrapper">
                <div id="heatmapDartboard" class="dartboard-d3"></div>
                <div id="heatmapTooltip" class="dartboard-tooltip">
                    <span class="tooltip-value">-</span>
                    <span class="tooltip-label">{{ __('popularity') }}</span>
                </div>
            </div>
            <script type="application/json" id="heatmapData">@json($dartboardHeat)</script>
        </div>

        <!-- Neighbour Averages -->
        <div class="info-card">
            <div class="info-card-header">
                <i class="fa-solid fa-arrows-to-circle"></i>
                <h3>{{ __('Strategic Target Zones') }}</h3>
            </div>
            <p class="mb-4">{{ __('Shows average values of nearby fields. Higher numbers indicate safer target zones - if you miss, nearby fields still score well.') }}</p>
            <div class="dartboard-wrapper">
                <div id="strategicDartboard" class="dartboard-d3"></div>
                <div id="strategicTooltip" class="dartboard-tooltip">
                    <span class="tooltip-value">-</span>
                    <span class="tooltip-label">{{ __('avg nearby') }}</span>
                </div>
            </div>
            <script type="application/json" id="strategicData">@json($dartboardAverages)</script>
            <script type="application/json" id="strategicHeatData">@json($dartboardAveragesHeat)</script>
            <div class="strategic-legend">
                <div class="legend-scale">
                    <span class="scale-low">{{ __('Lower value') }}</span>
                    <div class="scale-gradient"></div>
                    <span class="scale-high">{{ __('Higher value') }}</span>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
