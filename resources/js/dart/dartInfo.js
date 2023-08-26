/**
 * @author Maximilian Mewes
 *
 *
 */

import h337 from 'heatmap.js';
import Plotly from 'plotly.js-dist';

import * as UTILS from '../utils';
import Dartboard from './dartboard';
import DartCalculator from './dartCalculator';
import DartDefinition from './dartDefinition';

$(function()
{
    // optimazation: put in seperate file and load sync
    fetchDataRenderPlots();


    var polarArr = DartDefinition.fieldOrder20;
    polarArr.push(20);

    const data = [{
        r: polarArr,
        theta: polarArr.map( (f, i) => ((DartDefinition.fieldSizeDeg * i) > 360 ? 0 : DartDefinition.fieldSizeDeg * i)),
        fill: 'toself',
        type: 'scatterpolar',
    }];

    const layout = {
        polar: {
          radialaxis: {
            visible: true,
            range: [0, 22],
            angle: 90,
            tickangle: 90
          },
          angularaxis: {
            direction: "clockwise",
            dtick: 360 / 20
          }
        },
        showlegend: false,
        paper_bgcolor: 'rgba(0, 0, 0, 0)',
        plot_bgcolor: 'rgba(0, 0, 0, 0)',
    };

    const config = {
        displayModeBar: false, // this is the line that hides the bar.
    };

    Plotly.newPlot("graph01", data, layout, config);

});

function renderPlot(values)
{
    values = values.filter( (value, i) => !(value.sigma % 1) );

    const data = [{
        x: values.map( value => value.sigma),
        y: values.map( value => value.score),
        type: 'scatter',
        mode: 'lines+markers',
        line: {shape: 'spline'},
    }];

    const layout = {
        title: 'Expected Score Curve',
        xaxis: {
            type: 'log',
            title: 'Standard Deviation in mm',
            fixedrange: true,
            linecolor: 'rgba(255, 255, 255, .25)',
            gridcolor: 'rgba(255, 255, 255, .25)',
            zerolinecolor: 'rgba(255, 255, 255, .75)',
            tickfont: {
                color: 'rgba(255, 255, 255, .5)'
            },
        },
        yaxis: {
            title: 'Expected score',
            fixedrange: true,
            linecolor: 'rgba(255, 255, 255, .25)',
            gridcolor: 'rgba(255, 255, 255, .25)',
            zerolinecolor: 'rgba(255, 255, 255, .75)',
            tickfont: {
                color: 'rgba(255, 255, 255, .5)'
            },
        },
        annotations: [
            {
                x: 1.2,
                y: 20.6,
                xref: 'x',
                yref: 'y',
                text: 'T20 / T19',
                showarrow: true,
                arrowhead: 3,
                arrowcolor: 'rgba(255, 255, 255, .5)',
                ax: 30,
                ay: -40,
                font: {
                    size: 16,
                    color: 'rgba(255, 255, 255, .75)'
                },
            },
        ],
        paper_bgcolor: 'rgba(0, 0, 0, 0)',
        plot_bgcolor: 'rgba(0, 0, 0, 0)',
    }; // It's a stub, put layout config here.

    const config = {
        displayModeBar: false, // this is the line that hides the bar.
    };

    Plotly.newPlot('expectationDataGraph', data, layout, config);
}

function fetchDataRenderPlots()
{
    axios.get('/api/v1/dart/expectationData').then( response =>
        {
            let data = response.data.data.data;
            data = data.sort( (a,b) => a.score - b.score );

            renderPlot(data);
            renderPlotDartboard(data);

        }).catch(function (error) {
            if (error.response) {
                console.log(error.response.data);
            }
    });
}

function renderPlotDartboard(expData)
{
    let expData1 = expData.filter( d => d.sigma <= 16 );
    let expData2 = expData.filter( d => d.sigma > 16 );

    let size = 200;
    let matrix = [];
    for(let col = -size; col < size+1; col++) {
        let res = [];
        for(let row = -size; row < size+1; row++) {
            let points = DartCalculator.getScoreCartesian(row, col);
            res.push(points <= 0 ? null : points);
        }
        matrix.push(res);
    }

    let count = -1;
    let randomThrows = DartCalculator.generateRandomThrows(100, 20, size, x => x - size);
    console.table(DartCalculator.calculateStandardDeviation(randomThrows));
    randomThrows.forEach( t => {
        if(t.points == 25 || t.points == 50)
            count++;
    });
    // console.log(JSON.stringify(randomThrows.map( t => {
    //     return {
    //         x: t.x - size,
    //         y: t.y - size,
    //         points: t.points,
    //     };
    // })));
    console.table(`Treffer: ${count} / 100`);
    console.table(`Treffer: ${count / 2} / 50`);

    const data = [
        {
            z: matrix,
            hoverongaps: false,
            type: 'heatmap',
            name: 'scores',
        },{
            x: randomThrows.map( t => t.x ),
            y: randomThrows.map( t => t.y ),
            mode: 'markers',
            type: 'scatter',
            name: 'probability',
            marker: {
                size: 4
            },
        },{
            x: expData1.map( data => (data.x * size*2) + size+1),
            y: expData1.map( data => (data.y * size*2) + size+1),
            mode: 'lines',
            type: 'scatter',
            line: {
                color: 'rgb(0, 255, 0)',
                width: 4
            }
        },{
            x: expData2.map( data => (data.x * size*2) + size+1),
            y: expData2.map( data => (data.y * size*2) + size+1),
            mode: 'lines',
            type: 'scatter',
            line: {
                color: 'rgb(0, 255, 0)',
                width: 4
            }
        },
    ];

    const layout = {
        legend: {
            font: {
                size: 12,
                color: '#fff'
            },
        },
        showlegend: false,
        xaxis: {
            fixedrange: true,
            showgrid: false,
            zeroline: false,
            showline: false,
            autotick: true,
            // ticks: '',
            // showticklabels: false,
            zerolinecolor: '#fff',
            linecolor: '#fff',
            gridcolor: '#fff',
            tickfont: {
                // size: 14,
                color: '#fff'
            },
            rangemode: 'tozero',
        },
        yaxis: {
            fixedrange: true,
            showgrid: false,
            zeroline: false,
            showline: false,
            autotick: true,
            // ticks: '',
            // showticklabels: false,
            zerolinecolor: '#fff',
            linecolor: '#fff',
            gridcolor: '#fff',
            tickfont: {
                // size: 14,
                color: '#fff'
            },
        },
        paper_bgcolor: 'rgba(0, 0, 0, 0)',
        plot_bgcolor: 'rgba(0, 0, 0, 0)',
    };

    const config = {
        displayModeBar: false, // this is the line that hides the bar.
    };

    Plotly.newPlot('dartboardDataGraph', data, layout, config);
}
