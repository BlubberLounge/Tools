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

$(function()
{
    fetchHeatmapData();

    let dartboard = new Dartboard('#dartboardContainer');
    dartboard.render();

    // optimazation: put in seperate file and load sync
    fetchDataRenderPlots();

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

    let randomThrows = DartCalculator.generateRandomThrows(100, 20, size);
    const origin = {
        x: size+1,
        y: size+1,
    };

    let count = -1;
    console.table(DartCalculator.calculateStandardDeviation(origin, randomThrows));
    randomThrows.forEach( t => {
        let score = DartCalculator.getScoreCartesian(t.x - size, t.y - size);
        if(score == 25 || score == 50)
            count++;
    });
    console.table(`Treffer: ${count} / 100`);
    console.table(`Treffer: ${count / 2} / 50`);

    const data = [
        {
            z: matrix,
            hoverongaps: false,
            type: 'heatmap',
            name: 'scores',
        },{
            // x: [170, 180, 190, 200, 210, 210, 200, 190, 180, 170,],
            // y: [170, 180, 190, 200, 210, 170, 180, 190, 200, 210],
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

function placeMarker(dartboardContainer, x, y, className)
{
    let hitMarker = document.createElement("i");
    hitMarker.classList.add('hitMarker', className);

    let markerWidth = window.getComputedStyle(hitMarker).width;
    let markerHeight = window.getComputedStyle(hitMarker).height;

    let leftPosition = x - (markerWidth/2); // center hitMarker on cursor tip
    let topPosition = y - (markerHeight/2); // center hitMarker on cursor tip

    hitMarker.style.setProperty("top", topPosition+"px");
    hitMarker.style.setProperty("left", leftPosition+"px");

    // console.log('hit placed');
    // console.log(`X: ${x} Y: ${y}`);

    // document.getElementsByClassName('c-Dartboard')[0].append(hitMarker);
    dartboardContainer.append(hitMarker);

    return hitMarker;
}

/**
 *
 *
 */
async function fetchHeatmapData()
{
    let gameId = document.getElementById('gameId').getAttribute('value');
    // let gameId = '99ec098a-cd74-4223-ab1f-3bca516ce8fa';
    return axios.get('/api/v1/user/showThrowsByGame/'+gameId).then( response =>
    {
        renderHeatmap(response.data.data);

    }).catch(function (error) {
        if (error.response) {
            console.log(error.response.data);
            console.error('Throws could not get fetched!');
        }
    });
}

/**
 *
 *
 */
function renderHeatmap(data)
{
    var width = document.querySelector('#dartboardContainer').offsetWidth;
    var height = document.querySelector('#dartboardContainer').offsetHeight;

    // if(width != height)
    //     console.warn('Something is wrong!');

    // console.log(width);
    // console.log(height);

    let widthHalf = width / 2;
    let heightHalf = height / 2;
    let multiplier = Math.min(widthHalf, heightHalf);

    let dartboardContainer = document.querySelector('#dartboardContainer');
    var points = [];

    data.throws.forEach( w => {
        points.push({
            x: Math.round( widthHalf - (w.x * multiplier) * -1 ),
            y: Math.round( heightHalf - (w.y * multiplier) ),
            value: .5
        });
    });
    // console.table(points);

    let median = DartCalculator.calculateGeometricMedian(points, 4);
    // console.table(median);
    placeMarker(dartboardContainer, median.x, median.y, 'median');

    let centroid = DartCalculator.calculateGeometricCentroid(points);
    // console.table(centroid);
    placeMarker(dartboardContainer, centroid.x, centroid.y, 'centroid');

    let skeletonHeatmap = document.getElementById('skeleton-dartboard');
    if(skeletonHeatmap)
        skeletonHeatmap.remove();

    let heatmapInstance = h337.create({
        container: dartboardContainer,
        radius: 20,
    });

    let data1 = {
        max: 1,
        data: points
    };

    heatmapInstance.setData(data1);
}
