@extends('layouts.app')

@push('scripts')
    <script src="{{ mix('js/simApp.js') }}" defer></script>
@endpush

@section('content')
<div class="container">
    
    @include('battery.includes.title')

    <div class="row justify-content-between g-5 pb-1">
        <div class="col-3 border-end mb-5">
            <h1 class="mb-3">Initial Parameter</h1>
            <form id="form-start-parameter" class="form-horizontal">
                <fieldset id="fieldsetParameter">
                    <div class="row align-items-center">
                        <label for="settingPresets" class="col col-form-label">Presets:</label>
                        <div class="col-8">
                            <select class="form-select form-select-sm" id="settingPreset">
                                <option value="-1" class="text-muted" selected>no preset selected</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 align-items-center">
                        <label for="batteryMaxVoltage" class="col col-form-label">max. Voltage:</label>
                        <div class="col">
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" id="batteryMaxVoltage" min="0" step=".1" placeholder="4.2" value="4.2" aria-describedby="batteryMaxVoltageUnit">
                                <span class="input-group-text" id="batteryMaxVoltageUnit">v</span>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 align-items-center">
                        <label for="batteryMinVoltage" class="col col-form-label">min. Voltage:</label>
                        <div class="col">
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" id="batteryMinVoltage" min="0" max="12.6" step=".1" placeholder="2.7" value="2.7" aria-describedby="batteryMinVoltageUnit">
                                <span class="input-group-text" id="batteryMinVoltageUnit">v</span>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 align-items-center">
                        <label for="batteryCapacity" class="col col-form-label">Battery Capacity:</label>
                        <div class="col">
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" id="batteryCapacity" min="0" step="100" placeholder="3500" value="3500" aria-describedby="batteryCapacityUnit">
                                <span class="input-group-text" id="batteryCapacityUnit">mAh</span>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 align-items-center">
                        <label for="batteryLevel" class="col col-form-label">Battery Level:</label>
                        <div class="col">
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" id="batteryLevel" min="0" max="100" step="1" placeholder="100" value="100" aria-describedby="batteryLevelUnit">
                                <span class="input-group-text" id="batteryLevelUnit">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 align-items-center">
                        <label for="readInterval" class="col col-form-label">Interval:</label>
                        <div class="col">
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" id="readInterval" min="0" max="60000" step="500" placeholder="5000" value="5000"aria-describedby="readIntervalUnit">
                                <span class="input-group-text" id="readIntervalUnit">ms</span>
                            </div>
                        </div>
                    </div>
                    <div class="row g-3 align-items-center mb-3">
                        <label for="staticLoad" class="col col-form-label">Static load:</label>
                        <div class="col">
                            <div class="input-group input-group-sm">
                                <input type="number" class="form-control" id="staticLoad" min="0" step="50" placeholder="450" value="450" aria-describedby="staticLoadUnit">
                                <span class="input-group-text" id="staticLoadUnit">mA</span>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <div class="row">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-success" id="startBtn"><i class="fa-solid fa-play"></i></button> <!-- Start -->
                        <button type="button" class="btn btn-danger" id="stopBtn"><i class="fa-solid fa-stop"></i></button> <!-- Stop -->
                        <button type="button" class="btn btn-warning" id="pauseBtn"><i class="fa-solid fa-pause"></i></button> <!-- Pause -->
                    </div>
                </div>
            </form>
        </div>
        <div class="col-3 border-end">
            <h1 class="text-center">Information</h1>
            <table class="table table-sm">
                <tbody>
                    <tr>
                        <td width="40%">Time:</td>
                        <td id="currentTime" class="text-end" colspan="2">invalid</td>
                    </tr>
                    <tr>
                        <td>State:</td>
                        <td id="stateInfo" class="text-end fw-bold text-primary" colspan="2"> NOT STARTED </td>
                    </tr>
                    <tr>
                        <td>Started @</td>
                        <td id="startTime" class="text-end">invalid</td>
                        <td><i class="fa-regular fa-clock"></i></td>
                    </tr>
                    <tr>
                        <td>Voltage:</td>
                        <td id="currentVoltage" class="text-end">invalid</td>
                        <td>v</td>
                    </tr>
                    <tr>
                        <td>Capacity:</td>
                        <td id="currentCapacity" class="text-end">invalid</td>
                        <td>mAh</td>
                    </tr>
                    <tr>
                        <td>Percentage:</td>
                        <td id="currentPercentage" class="text-end">invalid</td>
                        <td><i id="currentPercentageUnit" class="fa-solid fa-battery-half fa-lg" style="color:green;"></i></td>
                    </tr>
                    <tr class="text-muted">
                        <td>Next update:</td>
                        <td id="currentNextUpdate" class="text-end">invalid</td>
                        <td>sec.</td>
                    </tr>
                    <tr>
                        <td>Time left:</td>
                        <td id="currentRemainingTime" class="text-end">invalid</td>
                        <td>min.</td>
                    </tr>
                    <tr>
                        <td>Discharge time:</td>
                        <td id="currentDischargeTime" class="text-end">invalid</td>
                        <td id="currentDischargeTimeUnit">hours</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-6 text-center position-relative">
            <h1>Discharge Curves</h1>
            
            {{-- <button type="button" class="btn btn-dark position-absolute end-0" data-bs-toggle="modal" data-bs-target="#exampleModal">
                <i class="fa-solid fa-gear"></i><span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary">+99 <span class="visually-hidden">unread messages</span></span>
            </button> --}}

            <canvas id="dischargeCurveChart" style="width:100%;max-width:600px"></canvas>
        </div>
    </div>
    <div class="row justify-content-between g-5">
        <div class="col border-end text-center">
            <h1> <i class="fa-solid fa-circle fa-2xs fa-fade text-danger me-2" style="font-size: .45em;"></i>Live Data</h1>
            <canvas id="liveDataChart" style="width:100%;max-height:300px;margin:0;"></canvas>
        </div>
        <div class="col-2">
            <h2 class="mb-3">Options</h2>
            <form id="form-options" class="form-horizontal">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="switchRealtimeMode" checked>
                    <label class="form-check-label" for="switchRealtimeMode">Realtime Sim</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="switchRandomDeviations" checked>
                    <label class="form-check-label" for="switchRandomDeviations">Random deviations</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="switchSmoothData" checked disabled>
                    <label class="form-check-label" for="switchSmoothData">Show Smoothed data</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="switchExponentialSmoothing" checked disabled>
                    <label class="form-check-label small-75" for="switchExponentialSmoothing">Exponential smoothing</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="switchMovingAverage" disabled>
                    <label class="form-check-label" for="switchMovingAverage">Moving average</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="switchWeightedMovingAverage" disabled>
                    <label class="form-check-label small-75" for="switchWeightedMovingAverage">Weighted moving average</label>
                </div>
                <hr class="my-2">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="switch50Marks" checked>
                    <label class="form-check-label" for="switch50Marks">Show 50% marks</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="switchCutOffs">
                    <label class="form-check-label" for="switchCutOffs">Show Cut-offs</label>
                </div>
                <hr class="my-2">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="switchDynamicUnits" checked disabled>
                    <label class="form-check-label" for="switchDynamicUnits">Dynamic units</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="switchIconUnits" disabled>
                    <label class="form-check-label" for="switchIconUnits">Icons as units</label>
                </div>
            </form>
        </div>
    </div>

    {{-- <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Avanced Settings</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                ...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div> --}}
</div>
@endsection