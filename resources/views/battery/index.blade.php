@extends('layouts.app')

@push('scripts')
    @vite(['resources/js/simApp.js'])
@endpush

@section('content')
<div class="container mx-auto px-4">

    @include('battery.includes.title')

    {{-- Top Section: Parameters | Info | Chart --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 pb-1">
        {{-- Parameters --}}
        <div class="lg:col-span-3 lg:border-r lg:border-base-300 mb-5 lg:pr-4">
            <h2 class="text-lg font-semibold mb-3">Initial Parameter</h2>
            <form id="form-start-parameter">
                <fieldset id="fieldsetParameter" class="space-y-2">
                    <div>
                        <label for="settingPresets" class="label text-xs">Presets:</label>
                        <select class="select select-bordered select-sm w-full" id="settingPreset">
                            <option value="-1" class="text-base-content/50" selected>no preset selected</option>
                        </select>
                    </div>
                    <div>
                        <label for="batteryMaxVoltage" class="label text-xs">max. Voltage:</label>
                        <div class="join w-full">
                            <input type="number" class="input input-bordered input-sm join-item w-full" id="batteryMaxVoltage" min="0" step=".1" placeholder="4.2" value="4.2">
                            <span class="btn btn-sm join-item no-animation">v</span>
                        </div>
                    </div>
                    <div>
                        <label for="batteryMinVoltage" class="label text-xs">min. Voltage:</label>
                        <div class="join w-full">
                            <input type="number" class="input input-bordered input-sm join-item w-full" id="batteryMinVoltage" min="0" max="12.6" step=".1" placeholder="2.7" value="2.7">
                            <span class="btn btn-sm join-item no-animation">v</span>
                        </div>
                    </div>
                    <div>
                        <label for="batteryCapacity" class="label text-xs">Battery Capacity:</label>
                        <div class="join w-full">
                            <input type="number" class="input input-bordered input-sm join-item w-full" id="batteryCapacity" min="0" step="100" placeholder="3500" value="3500">
                            <span class="btn btn-sm join-item no-animation">mAh</span>
                        </div>
                    </div>
                    <div>
                        <label for="batteryLevel" class="label text-xs">Battery Level:</label>
                        <div class="join w-full">
                            <input type="number" class="input input-bordered input-sm join-item w-full" id="batteryLevel" min="0" max="100" step="1" placeholder="100" value="100">
                            <span class="btn btn-sm join-item no-animation">%</span>
                        </div>
                    </div>
                    <div>
                        <label for="readInterval" class="label text-xs">Interval:</label>
                        <div class="join w-full">
                            <input type="number" class="input input-bordered input-sm join-item w-full" id="readInterval" min="0" max="60000" step="500" placeholder="5000" value="5000">
                            <span class="btn btn-sm join-item no-animation">ms</span>
                        </div>
                    </div>
                    <div>
                        <label for="staticLoad" class="label text-xs">Static load:</label>
                        <div class="join w-full">
                            <input type="number" class="input input-bordered input-sm join-item w-full" id="staticLoad" min="0" step="50" placeholder="450" value="450">
                            <span class="btn btn-sm join-item no-animation">mA</span>
                        </div>
                    </div>
                </fieldset>
                <hr class="my-3 border-base-300">
                <div>
                    <label for="currentLoad" class="label text-xs">Current load:</label>
                    <div class="join w-full">
                        <input type="number" class="input input-bordered input-sm join-item w-full" id="currentLoad" min="0" step="50" placeholder="255">
                        <span class="btn btn-sm join-item no-animation">mA</span>
                    </div>
                </div>
            </form>
        </div>

        {{-- Information --}}
        <div class="lg:col-span-3 lg:border-r lg:border-base-300 lg:pr-4">
            <h2 class="text-lg font-semibold text-center mb-3">Information</h2>
            <table class="table table-sm text-sm">
                <tbody>
                    <tr>
                        <td class="w-2/5">Time:</td>
                        <td id="currentTime" class="text-right" colspan="2">invalid</td>
                    </tr>
                    <tr>
                        <td>State:</td>
                        <td id="stateInfo" class="text-right font-bold text-primary" colspan="2"> NOT STARTED </td>
                    </tr>
                    <tr>
                        <td>Started @</td>
                        <td id="startTime" class="text-right">invalid</td>
                        <td class="w-6"><i class="fa-regular fa-clock"></i></td>
                    </tr>
                    <tr>
                        <td>Voltage:</td>
                        <td id="currentVoltage" class="text-right">invalid</td>
                        <td class="w-6">v</td>
                    </tr>
                    <tr>
                        <td>Capacity:</td>
                        <td id="currentCapacity" class="text-right">invalid</td>
                        <td class="w-6">mAh</td>
                    </tr>
                    <tr>
                        <td>Percentage:</td>
                        <td id="currentPercentage" class="text-right">invalid</td>
                        <td class="w-6"><i id="currentPercentageUnit" class="fa-solid fa-battery-half fa-lg text-success"></i></td>
                    </tr>
                    <tr>
                        <td>Time left:</td>
                        <td id="currentRemainingTime" class="text-right">invalid</td>
                        <td class="w-6">min.</td>
                    </tr>
                    <tr>
                        <td>Discharge time:</td>
                        <td id="currentDischargeTime" class="text-right">invalid</td>
                        <td class="w-6" id="currentDischargeTimeUnit">hours</td>
                    </tr>
                </tbody>
            </table>
            <div class="flex gap-1 mt-2">
                <button type="button" class="btn btn-success btn-sm" id="startBtn"><i class="fa-solid fa-play"></i></button>
                <button type="button" class="btn btn-error btn-sm" id="stopBtn"><i class="fa-solid fa-stop"></i></button>
                <button type="button" class="btn btn-warning btn-sm" id="pauseBtn"><i class="fa-solid fa-pause"></i></button>
            </div>
        </div>

        {{-- Discharge Curves Chart --}}
        <div class="lg:col-span-6 text-center">
            <h2 class="text-lg font-semibold mb-3">Discharge Curves</h2>
            <canvas id="dischargeCurveChart" class="w-full max-w-150 mx-auto"></canvas>
        </div>
    </div>

    {{-- Bottom Section: Live Data | Options --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mt-4">
        <div class="lg:col-span-10 text-center">
            <h2 class="text-lg font-semibold">
                <i class="fa-solid fa-circle fa-2xs fa-fade text-error mr-2" style="font-size: .45em;"></i>Live Data
            </h2>
            <canvas id="liveDataChart" class="w-full max-h-75"></canvas>
        </div>
        <div class="lg:col-span-2">
            <h3 class="text-base font-semibold mb-3">Options</h3>
            <form id="form-options" class="space-y-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input class="checkbox checkbox-primary checkbox-sm" type="checkbox" id="switchRealtimeMode" checked>
                    <span class="text-sm">Realtime Sim</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input class="checkbox checkbox-primary checkbox-sm" type="checkbox" id="switchRandomDeviations" checked>
                    <span class="text-sm">Random deviations</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input class="checkbox checkbox-primary checkbox-sm" type="checkbox" id="switchSmoothData" checked disabled>
                    <span class="text-sm">Show Smoothed data</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input class="checkbox checkbox-primary checkbox-sm" type="checkbox" id="switchExponentialSmoothing" checked disabled>
                    <span class="text-xs">Exponential smoothing</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input class="checkbox checkbox-primary checkbox-sm" type="checkbox" id="switchMovingAverage" disabled>
                    <span class="text-sm">Moving average</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input class="checkbox checkbox-primary checkbox-sm" type="checkbox" id="switchWeightedMovingAverage" disabled>
                    <span class="text-xs">Weighted moving average</span>
                </label>
                <hr class="my-2 border-base-300">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input class="checkbox checkbox-primary checkbox-sm" type="checkbox" id="switch50Marks" checked>
                    <span class="text-sm">Show 50% marks</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input class="checkbox checkbox-primary checkbox-sm" type="checkbox" id="switchCutOffs">
                    <span class="text-sm">Show Cut-offs</span>
                </label>
                <hr class="my-2 border-base-300">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input class="checkbox checkbox-primary checkbox-sm" type="checkbox" id="switchDynamicUnits" checked disabled>
                    <span class="text-sm">Dynamic units</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input class="checkbox checkbox-primary checkbox-sm" type="checkbox" id="switchIconUnits" disabled>
                    <span class="text-sm">Icons as units</span>
                </label>
            </form>
        </div>
    </div>
</div>
@endsection
