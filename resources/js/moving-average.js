import Plotly from 'plotly.js-dist';


$(function()
{
    const yData = [...Array(5).fill(10), ...Array(11).keys(), 5, ...Array(8).fill(20), 50, 60, 100, 80, 60, 50, ...Array(11).fill(10), 20, 30, 40, 50, 60, ...Array(20).fill(0), ...Array(3).fill(50), ...Array(3).fill(25), ...Array(3).fill(50), ...Array(3).fill(25), ...Array(3).fill(50), ...Array(3).fill(25), ...Array(3).fill(50), ...Array(3).fill(25), ...Array(10).fill(50), 60, 70, 80, 90,...Array(20).fill(100), 90, 80, 70, 60, 50, 40, 30, 20, 10, ...Array(10).fill(30), ...Array(3).fill(25), ...Array(40).keys()];
    // const yData = [2,4,6,8,12,14,16,18,20];
    const xData = [...Array(yData.length).keys()];

    const data = [{
        name: 'base data',
        x: xData,
        y: yData,
        type: 'scatter',
    },{
        name: 'simple moving average (SMA)',
        x: xData,
        y: SMA_data(yData),
        type: 'scatter',
    },{
        name: 'cumulative average (CA)',
        x: xData,
        y: CA_data(yData),
        type: 'scatter',
    },{
        name: 'weighted moving average (WMA)',
        x: xData,
        y: WMA_data(yData),
        type: 'scatter',
    },{
        name: 'exponential moving average (EMA)',
        x: xData,
        y: EMA_data(yData),
        type: 'scatter',
    },{
        name: 'exponential moving average (EMA)',
        x: xData,
        y: EMA__data(yData),
        type: 'scatter',
    }];

    const layout = {
        title: 'Moving average methods',
        xaxis: {
            fixedrange: true,
            linecolor: 'rgba(255, 255, 255, .25)',
            gridcolor: 'rgba(255, 255, 255, .25)',
            zerolinecolor: 'rgba(255, 255, 255, .75)',
            tickfont: {
                color: 'rgba(255, 255, 255, .5)'
            },
            rangemode: 'tozero',
        },
        yaxis: {
            fixedrange: true,
            linecolor: 'rgba(255, 255, 255, .25)',
            gridcolor: 'rgba(255, 255, 255, .25)',
            zerolinecolor: 'rgba(255, 255, 255, .75)',
            tickfont: {
                color: 'rgba(255, 255, 255, .5)'
            },
            rangemode: 'tozero',
        },
        paper_bgcolor: 'rgba(0, 0, 0, 0)',
        plot_bgcolor: 'rgba(0, 0, 0, 0)',
        // colorway : [
        //     '#1f77b4', '#144D75',   // blue, blue darker
        //     '#ff7f0e', '#BF5E0A',   // orange, orange darker
        //     '#2ca02c', '#1A611A',   // green, green darker
        //     '#d62728', '#961B1B',   // red, red darker
        // ],
        // DEFAULT COLORWAY
        // '#1f77b4',  // muted blue
        // '#ff7f0e',  // safety orange
        // '#2ca02c',  // cooked asparagus green
        // '#d62728',  // brick red
        // '#9467bd',  // muted purple
        // '#8c564b',  // chestnut brown
        // '#e377c2',  // raspberry yogurt pink
        // '#7f7f7f',  // middle gray
        // '#bcbd22',  // curry yellow-green
        // '#17becf'   // blue-teal
    };

    const config = {
        displayModeBar: false,
    };

    Plotly.newPlot('movingAveragePlot', data, layout, config);
});

function SMA_data(data)
{
    let resultData = [];
    const size = 5;

    data.forEach( (e, i) => {
        let sum = 0;
        for(let j = 0; j < size; j++) {
            const x = i - j;
            sum += data[x];
        }
        resultData.push( sum / size );
    });

    return resultData;
}

function CA_data(data)
{
    let resultData = [];

    data.forEach((e, i) => {
        if(i == 0) {
            resultData.push(0);
        } else {
            resultData.push( (e + (i+1) * resultData[i-1]) / (i+1));
        }
    });

    return resultData;
}

function WMA_data(data)
{
    let resultData = [];
    const weights = [.4, .3, .2, .075, .05]; // aka. Kernel
    const weightsTotal = weights.reduce((total, t) => total + t, 0);

    data.forEach( (e, i) => {
        let sum = 0;
        weights.forEach( (weight, j) => {
            const x = i - j; // - weights.length / 2;
            sum += data[x] * weight;
        });

        resultData.push(sum / weightsTotal);
    });

    return resultData;
}

function EMA_data(data)
{
    let resultData = [];
    let sum = 0;
    const alpha = .3;

    data.forEach( e => {
        sum = sum + alpha * (e - sum);
        resultData.push(sum);
    });

    return resultData;
}

function EMA__data(data)
{
    let resultData = [];
    let sum = 0;
    const alpha = .1;

    data.forEach( e => {
        sum = sum + alpha * (e - sum);
        resultData.push(sum);
    });

    return resultData;
}
