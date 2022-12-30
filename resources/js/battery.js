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


class battery
{
    constructor(maxVoltage, minVoltage, capacity, level)
    {
        this.type = 'unkown';
        this.maxVoltage = maxVoltage;
        this.minVoltage = minVoltage;
        this.capacity = capacity;
        this.level = level;
        console.log("Battery loaded.");
    }
}

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
        this.state = state;
        this.readInterval = readInterval;
        this.staticLoad = staticLoad;
        console.log("Simulation application loaded.");
    }

    addBattery(maxVoltage, minVoltage, capacity, level)
    {
        this.battery = new battery(maxVoltage, minVoltage, capacity, level); 
    }

    async loop()
    {
        // do
        // {

        // } while(true);

    }

    start()
    {
        if(this.state == 3) return;
        this.state = 1;
    }

    startTime()
    {
        
    }

}
 

function currentTime()
{  
    let Z = (n) => {
        return n < 10 ? "0" + n : n;
    };
    let d = new Date(); // new Date().toLocaleString();
    let dd = Z(d.getDay());
    let mm = Z(d.getMonth());
    let yyyy = Z(d.getFullYear());
    let hh = Z(d.getHours());
    let m = Z(d.getMinutes());
    let ss = Z(d.getSeconds()); 

    let date = "<span class='small text-muted'>"+ dd +"."+ mm +"."+ yyyy + "</span>";
    let time = hh +":"+ m +":"+ ss;
    let timeString = date +" - "+ time;

    setIH('currentTime', timeString);
    setTimeout(currentTime, 1000);
}

function initSim()
{
    var app = new simApp(0, getVal('readInterval'), getVal('staticLoad'));
    app.addBattery(getVal('batteryMaxVoltage'), getVal('batteryMinVoltage'), getVal('batteryCapacity'), getVal('batteryLevel'));
    
    // updates current time field
    currentTime();
}

initSim();












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