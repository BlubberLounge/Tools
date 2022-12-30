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
var setIH = (id, txt) => {
    getEl(id).innerHTML = txt;
};
var updateIfDifferent = (id, val) => {
    getEl(id).innerHTML == val ? 'invalid' : setIH(id, val); 
};
var Z = (n) => {    // adds a leading zero
    return n < 10 ? "0" + n : n;
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
    constructor(maxVoltage, minVoltage, capacity, level)
    {
        this.type = 'unkown';
        this.voltage = maxVoltage - minVoltage;
        this.capacity = capacity;   // calculated
        this.level = capacity;      // calculated

        this.initlevel = level;         // max. / inittial
        this.initCapacity = capacity;   // max. / inittial
        this.maxVoltage = maxVoltage;
        this.minVoltage = minVoltage;

        console.log("Battery loaded.");
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

    addBattery(maxVoltage, minVoltage, capacity, level)
    {
        this.battery = new battery(maxVoltage, minVoltage, capacity, level); 
    }

    async loop()
    {
        // update front-end infos
        this.displayInfo();

        if(this.state == 0)

        if(this.state == 1)
            console.log("running");
        
        if(this.state == 2)
            console.log("paused");
        
        if(this.state == 3)
            console.log("stopped");

        console.log('loop');
        setTimeout(() => {this.loop()}, 1000);
    }

    start()
    {
        if(this.state == 3 || this.state == 1) return;
        this.state = 1;
        this.startTime = timeNow();
        console.log("started");
    }

    pause()
    {
        this.state = 2;
        console.log("paused");
    }

    stop()
    {
        this.state = 3;
        console.log("stopped");
    }

    displayInfo()
    {
        updateIfDifferent('startTime', this.startTime);
        // updateIfDifferent('currentVoltage', this.battery.voltage);
        // updateIfDifferent('currentCapacity', this.battery.capacity);
        // updateIfDifferent('currentPercentage', this.battery.level);
        // updateIfDifferent('currentNextUpdate', 'not calculated');
        // updateIfDifferent('currentRemainingTime', 'not calculated');
    }

}

export var app = new simApp(0, getVal('readInterval'), getVal('staticLoad'));

generatePresets();











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
new Chart("myChart", {
type: "line",
data: {
    labels: xValues,
    datasets: [{
    data: [860,1140,1060,1060,1070,1110,1330,2210,7830,2478],
    borderColor: "red",
    fill: false
    }]
},
options: {
    legend: {display: false}
}
});

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