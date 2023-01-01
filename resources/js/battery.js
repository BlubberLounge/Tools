"use strict";
import Chart from 'chart.js/auto';
import 'chartjs-adapter-luxon';

var d = document;
var getEl = (id) => {
    return d.getElementById(id);
}
var getVal = (id) => {
    return getEl(id).value;
}
var setVal = (id, val) => {
    getEl(id).value = val;
};
var setIH = (id, val) => {
    getEl(id).innerHTML = typeof val === "number" ? val.toFixed(2) : val;   // round every number
};
var updateIHIfDifferent = (id, val) => {
    return (getEl(id).innerHTML == val ? null : setIH(id, val ?? 'invalid')) === null ? false : true;
};
var uInfo = (id, val, unit = undefined) => {
    return updateIHIfDifferent(id, val) || (typeof unit !== "undefined" ? updateIHIfDifferent(id+"Unit", unit) : false); 
};
var Z = (n) => {    // adds a leading zero
    return n < 10 ? "0" + n : n;
};
var flip = (obj) => {
    return Object.keys(obj).reduce((ret, key) => {
      ret[obj[key]] = key;
      return ret;
    }, {});
};

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

function generatePresets()
{
    var selEl = getEl('settingPreset');
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

function applyPreset(opt)
{
    Object.entries(settings[opt]).forEach(o =>
    {
        setVal(o[0], o[1]);
    });
}

function disableInputs()
{
    // Object.keys(settings['Default 18650']).forEach(o =>
    // {
    //     getEl(o).disabled = true;
    // });
    getEl('fieldsetParameter').disabled = true;
}

function enableInputs()
{
    // Object.keys(settings['Default 18650']).forEach(o =>
    // {
    //     getEl(o).disabled = false;
    // });
    getEl('fieldsetParameter').disabled = false;
}

var timeNow = () =>
{ 
    let d = new Date(); // new Date().toLocaleString();
    let hh = Z(d.getHours());
    let mm = Z(d.getMinutes());
    let ss = Z(d.getSeconds());
    return [hh, mm, ss].join(':');
}

var dateNow = () =>
{
    let d = new Date(); // new Date().toLocaleString();
    let dd = Z(d.getDate());
    let mm = Z(d.getMonth()+1);
    let yyyy = Z(d.getFullYear());
    return "<span class='small text-muted'>"+ [dd, mm, yyyy].join('.') + "</span>";;
}

var now = () =>
{
    return Date.now();
}

function currentTime()
{  
    let timeString = dateNow() +" - "+ timeNow();
    setIH('currentTime', timeString);
    setTimeout(currentTime, 1000);
}

/**
 * 
 * 
 * Charts / Graphs 
 * cool Graphical area of things and stuff
 * 
 * 
 */

var generateLabels = (max) => 
{
    let list = [];
    let lastMillis = 0;

    for(var i=0; i < max; i++) {
        let millis = list.length >= 1 ? lastMillis+100: now();
        lastMillis = millis;
        list.push(new Date(millis));
    }

    return list;
};

var generateSampleData = (num) =>
{
    let list = [];
    let lastMillis = 0;

    for(var i=0; i < num; i++) {
        let millis = list.length >= 1 ? lastMillis+100: now();
        lastMillis = millis;

        let el = {};
        el.x = new Date(millis);
        el.y = Math.floor(Math.random() * 101);
        
        list.push(el);
    }
    
    return list;
};
var CHART_COLORS = {
    red: 'rgb(255, 99, 132)',
    orange: 'rgb(255, 159, 64)',
    yellow: 'rgb(255, 205, 86)',
    green: 'rgb(75, 192, 192)',
    blue: 'rgb(54, 162, 235)',
    purple: 'rgb(153, 102, 255)',
    grey: 'rgb(201, 203, 207)'
 };

var dat = {
    labels: generateLabels(100),
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

var cfg = {
    // legend: {
    //     display: false
    // },
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
            suggestedMin: now(),
            suggestedMax: now()+100000,
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
var ddddd = () => {let d=[];for(var i=5000; i >= 0; i-=1) {d.push(i);} return d;}
var datDischarge = {
    labels: ddddd(),
    datasets: [
        {
            label: 'Linear Curve',
            data: [],//generateSampleData(60),
            borderColor: CHART_COLORS.yellow,
            fill: false,
            pointRadius: 0,
            cubicInterpolationMode: 'monotone',
            tension: 0.4
        },
        {
            label: 'Symmetric sigmoidal',
            data: [],//generateSampleData(60),
            borderColor: CHART_COLORS.red,
            fill: false,
            pointRadius: 0,
            cubicInterpolationMode: 'monotone',
            tension: 0.4
        },
        {
            label: 'Asymmetric sigmoidal',
            data: [],//generateSampleData(60),
            borderColor: CHART_COLORS.blue,
            fill: false,
            pointRadius: 0,
            cubicInterpolationMode: 'monotone',
            tension: 0.4
        }
    ]
};
// const data = [];
// const data2 = [];
// let prev = 100;
// let prev2 = 80;
// for (let i = 0; i < 1000; i++) {
//   prev += 5 - Math.random() * 10;
//   data.push({x: i, y: prev});
//   prev2 += 5 - Math.random() * 10;
//   data2.push({x: i, y: prev2});
// }
var cfgDischarge = {
    interaction: {
      intersect: false
    },
    scales: {
        x: {
            title: {
                display: true,
                text: 'Discharged Capacity (mAh)'
            },
            beginAtZero: true
        },
        y: {
            title: {
                display: true,
                text: 'Voltage'
            }
        }
    }
};


/**
 * represents a single battery cell
 * 
 */
class battery
{
    // https://www.batterypowertips.com/how-to-read-battery-discharge-curves-faq/
    // https://electronics.stackexchange.com/questions/107049/working-out-mah-from-current-and-time
    // Average consumption = (Consumption1 × Time1 + Consumption2 × Time2) / (Time1 + Time2)
    
    // To calculate the battery size for a varying load which requires I1 in the interval t1 and I2 in the remaining time:
    // Estimate the average load current — Iav = (I1 × t1 / t) + (I2 × [t - t1 / t]).
    // Substitute I = Iav in the equation for battery capacity of lithium-ion. B = 100 × I × t / (100 - q) where B is the battery capacity, I is the load current, t is the duration of power supply, and q is the percentage of charge which should remain in the battery after the discharge.
    
    underLoad = false;
    underSameLoadSince = 0;
    loadBuffer = [];
    log = [];

    constructor(maxVoltage, minVoltage, capacity, level)
    {
        this.type = 'unkown';
        this.voltage = parseFloat(maxVoltage);
        this.capacity = Number(capacity);   // calculated
        this.level = Number(level);      // calculated

        this.initlevel = this.level;         // max. / inittial
        this.initCapacity = this.capacity;   // max. / inittial, Nominal capacity
        this.maxVoltage = parseFloat(maxVoltage);
        this.minVoltage = parseFloat(minVoltage);
        this.cRate = undefined;
        this.internalResistance = undefined;

        console.log("Battery loaded.");
    }

    calculateStats()
    {
        if(this.loadBuffer.length < 1)
            return;

        // add current buffer content to the log/history array
        this.loadBuffer.forEach((e, i) => {
            if(!e.timer)    // ignore running periods / last period in the best case
                return;

            // clear buffer
            this.loadBuffer.splice(i, 1);

            this.log.push(e);

            this.capacity -= e.dischargeCurrent * (e.timer / 60 / 60 / 1000);
            // this.level = this.linearMap(this.voltage, this.minVoltage, this.maxVoltage); 
            this.level = this.linearMap(this.capacity, 0, this.initCapacity); 
        });
    }

    currentLoad(load)
    {
        if(load < 1) {
            this.underLoad = false;
            return;
        }
        
        this.addToBuffer(load);
        this.underLoad = true;
    }

    addToBuffer(dischargeCurrent)
    {
        if(this.loadBuffer.length >= 1)
            // trying to reduce buffer entries, especially when having static loads
            if(dischargeCurrent == this.loadBuffer[this.loadBuffer.length-1].dischargeCurrent) {
                // if the battery is under the same load for 3 sec go ahead
                if(now() - this.underSameLoadSince < 3000) {
                    return; 
                } else {
                    this.underSameLoadSince = now();
                }
            }
        
        // create buffer entry
        let l = {};
        l.startTime = now();
        l.endTime = undefined;
        l.timer = 0; // track load time for later calculation
        l.dischargeCurrent = dischargeCurrent;

        // fill last entries endTime and calculate the startTime and endTime delta = timer
        if(this.loadBuffer.length >= 1) {
            this.loadBuffer[this.loadBuffer.length-1].endTime = l.startTime+1; // in case some other js thing gets in between use last startTime + 1 be be more accurate
            this.loadBuffer[this.loadBuffer.length-1].timer = this.loadBuffer[this.loadBuffer.length-1].endTime - this.loadBuffer[this.loadBuffer.length-1].startTime;
        }

        this.loadBuffer.push(l);
    }

    calculateCRate(current)
    {
        return this.cRate = this.capacity / current;
    }

    calculateDischargeCurrent()
    {
        return this.cRate * this.capacity;
    }

    calculateRunTime()
    {
        return this.runTime = 1 / this.cRate;
    }

    linearMap(v, min, max)
    {
        let p = (v-min)*100/ (max-min); 
        return p >= 100 ? 100 : p <= 0 ? 0 : p;
    }

    /**
     * Symmetric sigmoidal approximation
     * https://www.desmos.com/calculator/7m9lu26vpy
     *
     * c - c / (1 + k*x/v)^3
     */
    sigmoidal(v, min, max)
    {
	// slow
	// uint8_t result = 110 - (110 / (1 + pow(1.468 * (voltage - minVoltage)/(maxVoltage - minVoltage), 6)));

	// steep
	// uint8_t result = 102 - (102 / (1 + pow(1.621 * (voltage - minVoltage)/(maxVoltage - minVoltage), 8.1)));

	// normal
        let result = 105 - (105 / (1 + Math.pow(1.724 * (v - min)/(max - min), 5.5)));
        return result >= 100 ? 100 : result;
    }

    /**
     * Asymmetric sigmoidal approximation
     * https://www.desmos.com/calculator/oyhpsu8jnw
     *
     * c - c / [1 + (k*x/v)^4.5]^3
     */
    asigmoidal(v, min, max)
    {
        let result = 101 - (101 / Math.pow(1 + Math.pow(1.33 * (v - min)/(max - min) ,4.5), 3));
        return result >= 100 ? 100 : result;
    }
}


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

        this.addBattery(getVal('batteryMaxVoltage'), getVal('batteryMinVoltage'), getVal('batteryCapacity'), getVal('batteryLevel'));

        this.liveDataChart = new Chart("liveDataChart", {
            type: "line",
            data: dat,
            options: cfg
        });

        this.dischargeCurveChart = new Chart("dischargeCurveChart", {
            type: "line",
            data: datDischarge,
            options: cfgDischarge
        });

        this.generateDischargeCurveData();

        // updates current time field
        currentTime();
        this.loop();

        getEl('startBtn').addEventListener('click', function() {
            app.start();
        });
        getEl('stopBtn').addEventListener('click', function() {
            app.stop();
        });
        getEl('pauseBtn').addEventListener('click', function() {
            app.pause();
        });
        console.log("Simulation application loaded.");
    }

    async loop()
    {
        if(now()-this.prevUpdateInfo >= this.updateInfoInterval) {
            this.prevUpdateInfo = now();
            // update front-end infos
            this.displayInfo();
            console.log('info update');
            this.battery.calculateStats();
            // this.dischargeTime = this.dynamicTimeUnit((this.battery.capacity/this.staticLoad), this.dischargeTime.unit);
            if(this.state == 1) this.addData(this.liveDataChart, this.battery.capacity);
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
        this.startTime = timeNow();

        this.displayInfo();     // immediately update the info
        this.updateBattery();   // update battery with new start parameter
        this.readInterval = getVal('readInterval');
        this.staticLoad = getVal('staticLoad');
        disableInputs();
        this.generateDischargeCurveData();
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
        this.battery = new battery(maxVoltage, minVoltage, capacity, level); 
    }

    updateBattery()
    {   // juut overwrite it for now
        this.battery = new battery(getVal('batteryMaxVoltage'), getVal('batteryMinVoltage'), getVal('batteryCapacity'), getVal('batteryLevel')); 
    }

    displayInfo()
    {
        if(updateIHIfDifferent('stateInfo', this.states[this.state].replace('_', ' ').toUpperCase()))
        {
            let sicL = getEl('stateInfo').classList;
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

        uInfo('startTime', this.startTime || 'not started');
        uInfo('currentVoltage', this.battery.voltage);
        uInfo('currentCapacity', this.battery.capacity);
        uInfo('currentPercentage', this.battery.level);
        // updateIHIfDifferent('currentNextUpdate', now()-this.prevUpdateInfo);
        uInfo('currentRemainingTime', 'not calculated');
        // uInfo('currentDischargeTime', this.dischargeTime.val, this.dischargeTime.unitString);
        this.dischargeTime = this.dynamicTimeUnit((this.battery.capacity/this.staticLoad), 3);
        updateIHIfDifferent('currentDischargeTime', this.dischargeTime.val);
        updateIHIfDifferent('currentDischargeTimeUnit', this.dischargeTime.unitString);
    }   

    addData(chart, val)
    {
        let data = chart.data;
        
        if (data.datasets.length > 0) {
            let el = {};
            el.x = new Date(now());
            el.y = val ?? Math.floor(Math.random() * 101);
            data.datasets[0].data.push(el);

            chart.update();
        }
    }

    generateDischargeCurveData()
    {
        let fncs = [this.battery.linearMap, this.battery.sigmoidal, this.battery.asigmoidal];

        fncs.forEach((f, i) =>
        {   
            this.dischargeCurveChart.data.datasets[i].data = [];
            let data = [];
            let index = i;
            for(var i=this.battery.maxVoltage; i > this.battery.minVoltage ; i-=.1)
            {
                let d = {};
                d.x = Number(((f(i, this.battery.minVoltage, this.battery.maxVoltage) / 100) * this.battery.capacity).toFixed(0));
                d.y = Number(i.toFixed(2));
                data.push(d);
            }
        
            data.forEach(e =>
            {
                this.dischargeCurveChart.data.datasets[index].data.push(e);
            });
        });

        this.dischargeCurveChart.update();
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

}

export var app = new simApp(0, getVal('readInterval'), getVal('staticLoad'));

generatePresets();



  new Chart("myChart2", {
    type: 'bar',
    data: {
        labels: generateLabels(10),
        datasets: [{
          label: 'My First Dataset',
          data: [65, 59, 80, 81, 56, 55, 40],
          backgroundColor: [
            'rgba(255, 99, 132, 0.2)',
            'rgba(255, 159, 64, 0.2)',
            'rgba(255, 205, 86, 0.2)',
            'rgba(75, 192, 192, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(201, 203, 207, 0.2)'
          ],
          borderColor: [
            'rgb(255, 99, 132)',
            'rgb(255, 159, 64)',
            'rgb(255, 205, 86)',
            'rgb(75, 192, 192)',
            'rgb(54, 162, 235)',
            'rgb(153, 102, 255)',
            'rgb(201, 203, 207)'
          ],
          borderWidth: 1
        }]
      }
  });