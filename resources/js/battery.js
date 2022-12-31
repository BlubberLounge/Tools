import Chart from 'chart.js/auto';

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

}

export var app = new simApp(0, getVal('readInterval'), getVal('staticLoad'));

generatePresets();

/**
 * 
 * 
 * Charts / Graphs 
 * cool Graphical area of things and stuff
 * 
 * 
 */

// as
var volVals = (max) => 
{
    let list = [];

    for(var i=0; i < max; i++) {
        list.push(i);
    }

    return list
};

new Chart("currentVoltageChart", {
    type: "line",
    data: {
        labels: volVals(50),
        datasets: [{
            data: [860,1140,1060,1060,1070,1110,1330,2210,7830,2478],
            borderColor: "red",
            fill: false
        }, {
            data: volVals(40),
            borderColor: "green",
            fill: true
        }]
    },
    options: {
        legend: {display: false}
    }
});









var xValues = [100,200,300,400,500,600,700,800,900,650];
const MONTHS = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December'
  ];
  
  function months(config) {
    var cfg = config || {};
    var count = cfg.count || 12;
    var section = cfg.section;
    var values = [];
    var i, value;
  
    for (i = 0; i < count; ++i) {
      value = MONTHS[Math.ceil(i) % 12];
      values.push(value.substring(0, section));
    }
  
    return values;
  }
const labels = months({count: 7});


const ctx = document.getElementById('myChart');
new Chart("myChart1", {
    type: 'bar',
    data: {
        labels: labels,
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
        labels: labels,
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