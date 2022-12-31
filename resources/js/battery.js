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
var Z = (n) => {    // adds a leading zero
    return n < 10 ? "0" + n : n;
};
var flip = (obj) => {
    return Object.keys(obj).reduce((ret, key) => {
      ret[obj[key]] = key;
      return ret;
    }, {});
}

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

var dat = {
    labels: generateLabels(100),
    datasets: [
        {
            label: 'Battery#1',
            data: [],//generateSampleData(60),
            borderColor: "red",
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
                text: 'millis'
            }
        },
        y: {
            title: {
                display: true,
                text: 'voltage'
            },
            beginAtZero: true
          }
    }
};


/**
 * represents a single battery cell
 * 
 */
class battery
{
    underLoad = false;
    underSameLoadSince = 0;
    loadBuffer = [];
    log = [];

    constructor(maxVoltage, minVoltage, capacity, level)
    {
        this.type = 'unkown';
        this.voltage = maxVoltage;
        this.capacity = capacity;   // calculated
        this.level = level;      // calculated

        this.initlevel = level;         // max. / inittial
        this.initCapacity = capacity;   // max. / inittial
        this.maxVoltage = maxVoltage;
        this.minVoltage = minVoltage;

        console.log("Battery loaded.");
    }

    // subtractCapacityByRunningTime(timeRan, load)
    // {

    // }

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

            this.capacity -= e.value / e.timer;
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

    addToBuffer(load)
    {
        if(this.loadBuffer.length >= 1)
            // trying to reduce buffer entries
            if(load == this.loadBuffer[this.loadBuffer.length-1].value) {
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
        l.value = load;

        // fill last entries endTime and calculate the startTime and endTime delta = timer
        if(this.loadBuffer.length >= 1) {
            this.loadBuffer[this.loadBuffer.length-1].endTime = l.startTime+1; // in case some other js thing gets in between use last startTime + 1 be be more accurate
            this.loadBuffer[this.loadBuffer.length-1].timer = this.loadBuffer[this.loadBuffer.length-1].endTime - this.loadBuffer[this.loadBuffer.length-1].startTime;
        }

        this.loadBuffer.push(l);
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

    constructor(state = 0, readInterval, staticLoad)
    {
        this.instance = this;
        this.state = state;
        this.readInterval = readInterval;
        this.staticLoad = staticLoad;

        this.addBattery(getVal('batteryMaxVoltage'), getVal('batteryMinVoltage'), getVal('batteryCapacity'), getVal('batteryLevel'));

        this.currentVoltageChart = new Chart("currentVoltageChart", {
            type: "line",
            data: dat,
            options: cfg
        });

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
            if(this.state == 1) this.addData(this.currentVoltageChart, this.battery.capacity);
        }

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
        disableInputs();
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

        updateIHIfDifferent('startTime', this.startTime);
        updateIHIfDifferent('currentVoltage', this.battery.voltage);
        updateIHIfDifferent('currentCapacity', this.battery.capacity);
        updateIHIfDifferent('currentPercentage', this.battery.level);
        updateIHIfDifferent('currentNextUpdate', 'not calculated');
        updateIHIfDifferent('currentRemainingTime', 'not calculated');
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
};

}

export var app = new simApp(0, getVal('readInterval'), getVal('staticLoad'));

generatePresets();









const ctx = document.getElementById('myChart');
new Chart("myChart1", {
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