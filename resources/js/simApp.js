/**
 * @author Maximilian Mewes
 * 
 * 
 */
import Chart from 'chart.js/auto';
import 'chartjs-adapter-luxon';
import * as UTILS from './utils';
import Battery from './battery';

// const UTILS = require('./utils.js');

// initial parameter setting presets
var settings = {
    'Default 18650': {
        'batteryMaxVoltage': 4.2,
        'batteryMinVoltage': 2.7,
        'batteryCapacity': 3500,
        'batteryLevel': 100,
        'readInterval': 5000,
        'staticLoad': 450,
    },
    'High load 18650':
    {
        'batteryMaxVoltage': 4.2,
        'batteryMinVoltage': 2.7,
        'batteryCapacity': 3500,
        'batteryLevel': 100,
        'readInterval': 10000,
        'staticLoad': 350000,
    },
    'low Capacity 18650':
    {
        'batteryMaxVoltage': 4.2,
        'batteryMinVoltage': 2.6,
        'batteryCapacity': 2600,
        'batteryLevel': 100,
        'readInterval': 10000,
        'staticLoad': 450,
    },
    '1/2 full 18650':
    {
        'batteryMaxVoltage': 4.2,
        'batteryMinVoltage': 2.7,
        'batteryCapacity': 3500,
        'batteryLevel': 50,
        'readInterval': 5000,
        'staticLoad': 450,
    },
    '2S3P Battery (18650)':
    {
        'batteryMaxVoltage': 3.6*2,
        'batteryMinVoltage': 2.6,
        'batteryCapacity': 2500*3,
        'batteryLevel': 100,
        'readInterval': 5000,
        'staticLoad': 450,
    },
};

function applyPreset(opt)
{
    Object.entries(settings[opt]).forEach(o =>
    {
        UTILS.setVal(o[0], o[1]);
    });
}

/**
 * 
 * 
 * Charts / Graphs 
 * cool Graphical area of things and stuff
 * 
 * 
 */

var ddddd = () => {let d=[];for(var i=4000; i >= 0; i-=1) {d.push(i);} return d;}



/**
 * main class
 * 
 */
class simApp
{
    // state enum
    // states = {
    //     NOT_STARTED,
    //     STARTED,
    //     PAUSED,
    //     STOPPED
    // };
    states = ['NOT_STARTED', 'STARTED', 'PAUSED', 'STOPPED'];
    
    cycleTime = 100; //ms

    updateInfoInterval = 500;// update Info section every xxx ms
    prevUpdateInfo = 0;

    runTime = 0;

    dischargeTime = {val: 0.0, unit: 3, unitString: 'hours'};



    constructor(state = 0, readInterval, staticLoad)
    {
        this.instance = this;
        this.state = state;
        this.readInterval = readInterval;
        this.staticLoad = staticLoad;

        this.addBattery(UTILS.getVal('batteryMaxVoltage'), UTILS.getVal('batteryMinVoltage'), UTILS.getVal('batteryCapacity'), UTILS.getVal('batteryLevel'));
        
        this.generatePresets();

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

        this.generateDCCData();

        // updates current time field
        UTILS.currentTime();
        this.loop();

        UTILS.onClick('startBtn', function() {
            app.start();
        });

        UTILS.onClick('stopBtn', function() {
            app.stop();
        });

        UTILS.onClick('pauseBtn', function() {
            app.pause();
        });

        console.log("Simulation application loaded.");
    }

    async loop()
    {
        if(UTILS.now()-this.prevUpdateInfo >= this.updateInfoInterval) { // update front-end infos
            this.prevUpdateInfo = UTILS.now();
            
            this.displayInfo();
            this.battery.calculateStats();
            this.dischargeTime = this.dynamicTimeUnit((this.battery.capacity/this.staticLoad), 3); // 3 = hours, because capacity is in mAh

            console.log('info update');
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
        if(this.state == 3 || this.state == 1) return;
        this.state = 1;
        this.startTime = UTILS.timeNow();

        this.displayInfo();     // immediately update the info
        this.updateBattery();   // update battery with new start parameter
        this.readInterval = UTILS.getVal('readInterval');
        this.staticLoad = UTILS.getVal('staticLoad');
        
        UTILS.disableInputs();

        this.generateDCCData();
    }

    pause()
    {
        this.state = 2;
        this.displayInfo();     // immediately update the info
    }

    stop()
    {
        this.state = 3;
        this.displayInfo();     // immediately update the info
        enableInputs();
    }

    addBattery(maxVoltage, minVoltage, capacity, level)
    {
        this.battery = new Battery(maxVoltage, minVoltage, capacity, level); 
    }

    updateBattery()
    {   // juut overwrite it for now
        this.battery = new Battery(UTILS.getVal('batteryMaxVoltage'), UTILS.getVal('batteryMinVoltage'), UTILS.getVal('batteryCapacity'), UTILS.getVal('batteryLevel')); 
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

        UTILS.uInfo('startTime', this.startTime || 'not started');
        UTILS.uInfo('currentVoltage', this.battery.voltage);
        UTILS.uInfo('currentCapacity', this.battery.capacity);
        UTILS.uInfo('currentPercentage', this.battery.level);
        // updateIHIfDifferent('currentNextUpdate', now()-this.prevUpdateInfo);
        UTILS.uInfo('currentRemainingTime', 'not calculated');
        UTILS.uInfo('currentDischargeTime', this.dischargeTime.val, 3);
    }   

    addData(chart, val)
    {
        let data = chart.data;
        
        if (data.datasets.length > 0) {
            let el = {};
            el.x = new Date(UTILS.now());
            el.y = val ?? Math.floor(Math.random() * 101);
            data.datasets[0].data.push(el);

            chart.update();
        }
    }
    
    generateLDCData ()
    {
        let list = [];
        let lastMillis = 0;

        for(var i=0; i < 100; i++) {
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

    generateDCCData()
    {
        let fncs = [this.battery.linearMap, this.battery.sigmoidal, this.battery.asigmoidal];

        fncs.forEach((f, i) =>
        {   
            this.dcc.data.datasets[i].data = [];
            let data = [];
            let index = i;
            for(var i=this.battery.maxVoltage; i >= this.battery.minVoltage ; i-=.1)
            {
                let d = {};
                d.x = Number(((f(i, this.battery.minVoltage, this.battery.maxVoltage) / 100) * this.battery.capacity).toFixed(0));
                d.y = Number(i.toFixed(2));
                data.push(d);
            }
        
            data.forEach(e =>
            {
                this.dcc.data.datasets[index].data.push(e);
            });
        });

        this.dcc.update();
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

        return res;
    }

    generatePresets()
    {
        var selEl = UTILS.getEl('settingPreset');
        Object.keys(settings).forEach(opt =>
        { 
            let option = document.createElement("option");
            option.value = opt;
            option.text = opt;
            selEl.appendChild(option);
        });

        selEl.options[0].remove();
        applyPreset(Object.keys(settings)[0]);
        
        selEl.addEventListener('change', function() {
            applyPreset(this.value);
        });
    }

    ldcConfig = {
        scales: {
            x: {
                type: 'time',
                time: {
                    unit: 'millisecond',
                    tooltipFormat: 'YYYY-MM-DD HH:mm',
                    displayFormats: {
                        millisecond: 'HH:mm:ss.SSS',
                        second: 'HH:mm:ss',
                        minute: 'HH:mm',
                        hour: 'HH'
                    }
                },
                ticks: {
                    stepSize: 1000
                },
                title: {
                    display: true,
                    text: 'milliseconds (ms)'
                },
                suggestedMin: UTILS.now(),
                suggestedMax: UTILS.now()+10000,
            },
            y: {
                title: {
                    display: true,
                    text: 'Capacity (mAh)'
                },
                beginAtZero: true
              }
        }
    };

    initDDCData = {
        labels: ddddd(),
        datasets: [
            {
                label: 'Linear Curve',
                data: [],//generateSampleData(60),
                borderColor: UTILS.CHART_COLORS.yellow,
                fill: false,
                pointRadius: 0,
                cubicInterpolationMode: 'monotone',
                tension: 0.4
            },
            {
                label: 'Symmetric sigmoidal',
                data: [],//generateSampleData(60),
                borderColor: UTILS.CHART_COLORS.red,
                fill: false,
                pointRadius: 0,
                cubicInterpolationMode: 'monotone',
                tension: 0.4
            },
            {
                label: 'Asymmetric sigmoidal',
                data: [],//generateSampleData(60),
                borderColor: UTILS.CHART_COLORS.blue,
                fill: false,
                pointRadius: 0,
                cubicInterpolationMode: 'monotone',
                tension: 0.4
            }
        ]
    };

    ddcConfig = {
        interaction: {
          intersect: false
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Discharged Capacity (mAh)'
                },
                beginAtZero: true,
                suggestedMin: -500,
                suggestedMax: 4500
            },
            y: {
                title: {
                    display: true,
                    text: 'Voltage'
                }
            }
        }
    };
}

var app = new simApp(0, UTILS.getVal('readInterval'), UTILS.getVal('staticLoad'));