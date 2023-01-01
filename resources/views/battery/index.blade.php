@extends('layouts.app')

@section('content')
<div class="container">
    
    @include('battery.includes.title')

    <div class="row justify-content-between g-5">
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
                                <input type="number" class="form-control" id="batteryCapacity" min="0" max="4000" step="100" placeholder="3500" value="3500" aria-describedby="batteryCapacityUnit">
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
                                <input type="number" class="form-control" id="staticLoad" min="0" max="2000" step="50" placeholder="450" value="450" aria-describedby="staticLoadUnit">
                                <span class="input-group-text" id="staticLoadUnit">mA</span>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <div class="row">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-success" id="startBtn">Start</button>
                        <button type="button" class="btn btn-danger" id="stopBtn">Stop</button>
                        <button type="button" class="btn btn-warning" id="pauseBtn">Pause</button>
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
                        <td>%</td>
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
        <div class="col-6 text-center">
            <h1> <i class="fa-solid fa-circle fa-2xs fa-fade text-danger me-2" style="font-size: .45em;"></i>Live Data</h1>
            <canvas id="liveDataChart" style="width:100%;max-width:800px"></canvas>
        </div>
    </div>
    <div class="row justify-content-between g-5">
        <div class="col-6">
            <h1>Other cool stuff</h1>
            <canvas id="myChart2" style="width:100%;max-width:500px"></canvas>
        </div>
        <div class="col-6">
            <h1>Discharge Curves</h1>
            <canvas id="dischargeCurveChart" style="width:100%;max-width:600px"></canvas>
        </div>
    </div>
</div>
@endsection