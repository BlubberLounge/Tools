/**
 * @author Maximilian Mewes
 * 
 * 
 */
import Chart from 'chart.js/auto';
import { DateTime } from "luxon";
import 'chartjs-adapter-luxon';
import annotationPlugin from 'chartjs-plugin-annotation';
import * as UTILS from './utils';
import LiPo from './battery/lipo';
import LiOn from './battery/lion';
Chart.register(annotationPlugin);

var ddddd = () => {let d=[];for(var i=100; i >= 0; i-=1) {d.push(i);} return d;}

var app;
var delayed;

/**
 * main class
 * 
 */
class SimApp
{
    // state enum
    states = [
        'NOT_STARTED',
        'STARTED',
        'PAUSED',
        'STOPPED'
    ];
    
    cycleTime = 100; //ms

    updateInfoInterval = 500;// update Info section every xxx ms
    prevUpdateInfo = 0;

    runTime = 0;
    startTime = UTILS.now();

    dischargeTime = {
        val: 0.0, 
        unit: 3, 
        unitString: 'hours'
    };

    // exponentialSmoothing = {};
    // movingAverage = {};
    // weightedMovingAverage = {};

    // Settings / Options
    mode = ['REALTIME', 'QUICK'];

    show50Marks = false;
    showCutOffMarks = false;
    dynamicUnits = false;
    iconsAsUnits = true;


    constructor(state = 0, readInterval, staticLoad)
    {
        this.instance = this;
        this.state = state;
        this.readInterval = readInterval;
        this.staticLoad = staticLoad;

        this.battery = new LiPo(UTILS.getVal('batteryMaxVoltage'), UTILS.getVal('batteryMinVoltage'), UTILS.getVal('batteryCapacity'), UTILS.getVal('batteryLevel'));
        
        this.generatePresets();

        this.ldcConfig = {
            animations: {
                y: { duration: 0 },
            },
            scales: {
                x: {
                    type: 'time',//'timeseries',
                    time: {
                        unit: 'millisecond',
                        tooltipFormat: 'YYYY-MM-DD HH:mm',
                        displayFormats: {
                            millisecond: 'ss.SSS',
                            second: 'HH:mm:ss',
                            minute: 'HH:mm',
                            hour: 'HH'
                        }
                    },
                    ticks: {
                        stepSize: 100,
                        callback: (val) => {return val - this.startTime}
                    },
                    title: {
                        display: true,
                        text: 'Time in milliseconds (ms)'
                    }
                    // suggestedMin: UTILS.now(),
                    // suggestedMax: UTILS.now()+10000,
                },
                y: {
                    title: {
                        display: true,
                        text: 'Capacity (%)'
                    },
                    beginAtZero: true
                }
            }
        };

        this.initDDCData = {
            labels: ddddd(),
            datasets: [
                {
                    hidden: false,
                    label: 'Linear Curve',
                    data: this.generateDCCData(0),//generateSampleData(60),
                    // fill: {
                    //     target: {
                    //         value: 2.8,
                    //     },
                    //     below: 'red',
                    //     above: UTILS.chartGradient,
                    // },
                    fill: true,
                    backgroundColor: UTILS.chartGradient,
                    pointBackgroundColor: 'rgba(189, 195, 199)',
                },
                {
                    hidden: true,
                    label: 'Symmetric sigmoidal',
                    data: this.generateDCCData(1),//generateSampleData(60),
                    borderColor: UTILS.CHART_COLORS.red,
                    fill: true,
                },
                {
                    hidden: true,
                    label: 'Asymmetric sigmoidal',
                    data: this.generateDCCData(2),//generateSampleData(60),
                    borderColor: '#fff',//UTILS.CHART_COLORS.blue,
                    fill: true,
                    backgroundColor: UTILS.chartGradient(),
                    pointBackgroundColor: 'rgba(189, 195, 199)',
                }
            ]
        };

        this.ddcConfig = {
            animation: {
                onComplete: () => {
                    delayed = true;
                },
                delay: (context) => {
                    let delay = 0;
                    if (context.type === 'data' && context.mode === 'default' && !delayed) {
                        delay = context.dataIndex * 150 + context.datasetIndex * 50;
                    }
                    return delay;
                },
            },
            radius: 4,
            hitRadius: 30,
            hoverRadius: 10,
            cubicInterpolationMode: 'monotone',
            tension: 0.35,
            plugins: {
                autocolors: false,
                annotation: {
                    annotations: this.generateAnnotations()
                },
            },     
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Remaining Capacity (%)'//'Discharged Capacity (mAh)'
                    },
                    // beginAtZero: true,
                    // suggestedMin: -500,
                    // suggestedMax: 4500
                },
                y: {
                    title: {
                        display: true,
                        text: 'Voltage',
                    },
                    // beginAtZero: true,
                    // min: 2.8,
                }
            }
        };

        // ldc = LiveDataChart
        this.ldc = new Chart("liveDataChart", {
            type: "line",
            data: this.generateLDCData(),
            options: this.ldcConfig
        });

        // dcc = DischargeCurveChart
        this.dcc = new Chart("dischargeCurveChart", {
            type: "line",
            data: this.initDDCData,
            options: this.ddcConfig
        });

        // updates current time field
        UTILS.currentTime();
        this.loop();

        UTILS.onClick('startBtn', function(e) {
            app.start();
        });

        UTILS.onClick('stopBtn', function(e) {
            app.stop();
        });

        UTILS.onClick('pauseBtn', function(e) {
            app.pause();
        });

        UTILS.toggleChartAnnotaion('switch50Marks', 1, this.dcc);
        UTILS.toggleChartAnnotaion('switchCutOffs', 2, this.dcc);

        console.log("Simulation application loaded.");
    }

    async loop()
    {
        if(UTILS.now()-this.prevUpdateInfo >= this.updateInfoInterval) { // update front-end infos
            this.prevUpdateInfo = UTILS.now();
            
            this.displayInfo();
            this.battery.calculateStats();
            this.dischargeTime = this.dynamicTimeUnit((this.battery.capacity/this.staticLoad), 3); // 3 = hours, because capacity is in mAh

            if(this.state == 1) this.addData(this.ldc, this.battery.capacity);
        }

        if(this.battery.capacity <= 0)
            this.state = 2;

        if(this.state == 1) {
            this.battery.currentLoad(this.staticLoad);
        }   

        // if(this.state == 2)
        //     console.log("paused");
        
        // if(this.state == 3)
        //     console.log("stopped");


        // console.log('loop');
        setTimeout(() => {this.loop()}, this.cycleTime);
    }

    start()
    {
        if(this.state == 1 || this.state == 3) return;
        this.state = 1;
        this.startTime = UTILS.now();

        this.displayInfo();     // immediately update the info
        this.updateBattery();   // update battery with new start parameter
        this.readInterval = UTILS.getVal('readInterval');
        this.staticLoad = UTILS.getVal('staticLoad');
        
        UTILS.disableInputs();

        // this.generateDCCData();
    }

    pause()
    {
        if(this.state == 1) {
            this.state = 2;
            this.displayInfo();     // immediately update the info
        }
    }

    stop()
    {
        if(this.state == 0 || this.state == 3) return;
        this.state = 3;
        this.displayInfo();     // immediately update the info
        UTILS.enableInputs();
    }

    updateBattery()
    {   // juut overwrite it for now
        this.battery = new LiPo(UTILS.getVal('batteryMaxVoltage'), UTILS.getVal('batteryMinVoltage'), UTILS.getVal('batteryCapacity'), UTILS.getVal('batteryLevel')); 
    }

    displayInfo()
    {
        if(UTILS.updateIHIfDifferent('stateInfo', this.states[this.state].replace('_', ' ').toUpperCase()))
        {
            let sicL = UTILS.getEl('stateInfo').classList;
            switch(this.state)
            {
                case 0:
                    sicL.remove('text-success', 'text-danger', 'text-warning');
                    sicL.add('text-primary');
                    break;
                case 1:
                    sicL.remove('text-primary', 'text-danger', 'text-warning');
                    sicL.add('text-success');
                    break;
                case 2:
                    sicL.remove('text-primary', 'text-success', 'text-danger');
                    sicL.add('text-warning');
                    break;
                case 3:
                    sicL.remove('text-primary', 'text-success', 'text-warning');
                    sicL.add('text-danger');
                    break;
                default:
                break;
            }
        }

        UTILS.uInfo('startTime', UTILS.msToString(this.startTime) || 'not started');
        UTILS.uInfo('currentVoltage', this.battery.voltage);
        UTILS.uInfo('currentCapacity', this.battery.capacity);
        if(UTILS.uInfo('currentPercentage', this.battery.level))
        {   // TODO: optimize
            let crgb = new UTILS.CRGB().color;
            let col = null;
            if(this.battery.level <= 33) {
                col = crgb.danger;
            } else if(this.battery.level <= 66) {
                col = crgb.warning;
            } else {
                col = crgb.success;
            }

            UTILS.getEl('currentPercentageUnit').style.color = col;
            let cl = UTILS.getEl('currentPercentageUnit').classList;
            const l = ['fa-battery-empty', 'fa-battery-quarter', 'fa-battery-half', 'fa-battery-three-quarters', 'fa-battery-full'];
            
            cl.remove('fa-battery-empty', 'fa-battery-quarter', 'fa-battery-half', 'fa-battery-three-quarters', 'fa-battery-full');
            if(this.battery.level <= 10) {
                cl.add(l[0]);
            }else if(this.battery.level <= 33) {
                cl.add(l[1]);
            } else if(this.battery.level <= 50) {
                cl.add(l[2]);
            } else if(this.battery.level <= 66) {
                cl.add(l[3]);
            } else if(this.battery.level > 75) {
                cl.add(l[4]);
            }
        }
        // updateIHIfDifferent('currentNextUpdate', now()-this.prevUpdateInfo);
        UTILS.uInfo('currentRemainingTime', 'not calculated');
        UTILS.uInfo('currentDischargeTime', this.dischargeTime.val, this.dischargeTime.unitString);
    }

    addData(chart, val)
    {
        let data = chart.data;
        
        if (data.datasets.length > 0) {
            let el = {};
            
            // data.labels = new Date(UTILS.now()+100);

            el.x = new Date(UTILS.now());
            el.y = val ?? Math.floor(Math.random() * 101);
            data.datasets[0].data.push(el);

            // chart.update('none');
            chart.update();
        }
    }

    generateAnnotations()
    {
        
        var thing = (x) =>
        {
            let delta = (a, b) =>
            {
                return a < b ? b-a : a-b;
            }
            let d = this.generateDCCData(0);
            let c1 = d[0];
            let c2 = d[d.length-1];
            let xDelta = delta(c1.x, c2.x);
            let yDelta = delta(c1.y, c2.y);
            let g = yDelta/xDelta;
            let c = c2.y-(g*c2.x);
            let f = g*x+c;
            return f;
        };

        const SOCrpower = Math.sqrt(UTILS.integral(thing, 0, 100));
        const SOCpower = 100-Math.sqrt(UTILS.integral(this.battery.linearMap, 0, 100));

        return {            
            COlow: {  
                label: {
                    backgroundColor: 'green',
                    content: 'Cut-Off', //[(100-SOCpower).toFixed(1)+'%', 'SOC power'],
                    display: true,
                    xAdjust: 200,                                
                    font: {
                        size: 9
                    },
                },
                display: (c, o) =>
                {
                    return UTILS.displayAnnotation(c, o);
                },
                type: 'line',
                yMin: this.battery.minVoltage,
                yMax: this.battery.minVoltage,
                borderDash: [5, 5],
                borderColor: 'green',
                borderWidth: 2,
            },            
            COhigh: {  
                label: {
                    backgroundColor: 'green',
                    content: 'Cut-Off', //[(100-SOCpower).toFixed(1)+'%', 'SOC power'],
                    display: true,
                    xAdjust: 200,                                
                    font: {
                        size: 9
                    },
                },
                display: (c, o) =>
                {
                    return UTILS.displayAnnotation(c, o);
                },
                type: 'line',
                yMin: this.battery.maxVoltage,
                yMax: this.battery.maxVoltage,
                borderDash: [5, 5],
                borderColor: 'green',
                borderWidth: 2,
            },
            A50: {                              
                label: {
                    backgroundColor: 'red',
                    content: ['50%', 'SOC capacity'],
                    display: true,
                    yAdjust: 80,                        
                    font: {
                        size: 9
                    },
                },
                display: (c, o) =>
                {
                    return UTILS.displayAnnotation(c, o);
                },
                type: 'line',
                xMin: 50,
                xMax: 50,
                // yMin: 2.6,
                // yMax: 4.2,
                borderDash: [5, 5],
                borderColor: 'rgb(255, 99, 132)',
                borderWidth: 2,
            },
            SOCrpower: {  
                label: {
                    backgroundColor: 'orange',
                    content: ['50%', 'SOC usable power'],//[(100-SOCrpower).toFixed(1)+'%', 'usable'],
                    display: true,
                    yAdjust: 45,                                
                    font: {
                        size: 9
                    },
                },                    
                display: (c, o) =>
                {
                    return UTILS.displayAnnotation(c, o);
                },
                type: 'line',
                xMin: SOCrpower,
                xMax: SOCrpower,
                // yMin: 2.6,
                // yMax: 4.2,
                borderDash: [5, 5],
                borderColor: 'orange',
                borderWidth: 2,
            },
            SOCpower: {  
                label: {
                    backgroundColor: 'blue',
                    content: ['50%', 'SOC power'], //[(100-SOCpower).toFixed(1)+'%', 'SOC power'],
                    display: true,
                    yAdjust: 85,                                
                    font: {
                        size: 9
                    },
                },
                display: (c, o) =>
                {
                    return UTILS.displayAnnotation(c, o);
                },
                type: 'line',
                xMin: SOCpower,
                xMax: SOCpower,
                // yMin: 2.6,
                // yMax: 4.2,
                borderDash: [5, 5],
                borderColor: 'blue',
                borderWidth: 2,
            }
        };
    }
    
    generateLDCData ()
    {
        let list = [];
        let lastMillis = 0;

        for(var i=0; i < 250; i++) {
            let millis = list.length >= 1 ? lastMillis+100 : UTILS.now();
            lastMillis = millis;
            list.push(new Date(millis));
        }

        let data = {
            labels: list,
            datasets: [
                {
                    label: 'Battery#1',
                    data: [],//generateSampleData(60),
                    borderColor: 'red',//CHART_COLORS.green,
                    // borderWidth: 3,
                    fill: false,
                    pointStyle: 'crossRot',
                    pointRadius: 0,//5
                    // pointHoverRadius: 8,
                    cubicInterpolationMode: 'monotone',
                    tension: 0.4
                }
            ]
        };

        return data;
    };

    generateDCCData(id)
    {
        let f = [this.battery.linearMap, this.battery.sigmoidal, this.battery.asigmoidal];

        var data = [];
        for(var i=this.battery.maxVoltage; i >= this.battery.minVoltage-.1; i-=.1)
        {
            let d = {};
            d.x = Number(((f[id](i, this.battery.minVoltage, this.battery.maxVoltage)).toFixed(0)));//Number(((f(i, this.battery.minVoltage, this.battery.maxVoltage) / 100) * this.battery.capacity).toFixed(0));
            d.y = Number(i.toFixed(2));
            data.push(d);
        }
        
        //     data.forEach(e =>
        //     {
        //         this.dcc.data.datasets[index].data.push(e);
        //     });

        // this.dcc.update();

        return data;
    }

    dynamicTimeUnit(time, unitId)
    {        
        let unitString = [
            'ms',
            'sec',
            'min',
            'hour',
            'day'
        ];

        let t = 0;
        let res = {};

        // normilize time to ms
        switch(unitId)
        {
            case 1: // seconds
                t = time * 1000;
                break;
            case 2: // minutes
                t = time * 60 * 1000;
                break;
            case 3: // hours
                t = time * 60 * 60 * 1000;
                break;            
            case 4: // days
                t = time * 24 * 60 * 60 * 1000;
                break;
            case 0: // milliseconds
            default:
                t = time;
                break;
        }

        if(t <= 1000) { // milliseconds
            res.val = t;
            res.unit = 0;
            res.unitString = unitString[0];
        }else if(t <= (60*1000)) { // seconds
            res.val = t / 1000;
            res.unit = 1;
            res.unitString = unitString[1]; 
        }else if(t <= (60*60*1000)) { // minutes
            res.val = t / 60 / 1000;
            res.unit = 2;
            res.unitString = unitString[2];
        }else if(t <= (24*60*60*1000)) { // hours
            res.val = t / 60 / 60 / 1000;
            res.unit = 3;
            res.unitString = unitString[3];
        }else if(t > (24*60*60*1000)) { // days
            res.val = t / 24 / 60 / 60 / 1000;
            res.unit = 4;
            res.unitString = unitString[4];
        }

        if(res.val > 1 && t > 1000)
            res.unitString = res.unitString + "s";

        res.val = res.val <= 0 ? 0 : Math.abs(res.val);

        return res;
    }

    generatePresets()
    {
        var selEl = UTILS.getEl('settingPreset');
        Object.keys(this.battery.availablePresets).forEach(opt =>
        { 
            let option = document.createElement("option");
            option.value = opt;
            option.text = opt;
            selEl.appendChild(option);
        });

        selEl.options[0].remove();
        UTILS.applyPreset(this.battery.availablePresets, Object.keys(this.battery.availablePresets)[0]);
        
        UTILS.onChange('settingPreset', function() {
            UTILS.applyPreset(app.battery.availablePresets, this.value);
        });
    }

}

window.addEventListener('DOMContentLoaded', () =>
{
    app = new SimApp(0, UTILS.getVal('readInterval'), UTILS.getVal('staticLoad'));
});