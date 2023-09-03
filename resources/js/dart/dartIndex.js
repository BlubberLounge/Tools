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
    const dartboard = new Dartboard('#dartboardContainer');
    dartboard.render();

    const radarChart = document.getElementById('graph01');
    initRadarChart(radarChart);

    document.getElementById('gameSelection').addEventListener('change', function()
    {
        fetchData(this.value);
    });

    fetchData(document.getElementById("gameSelection").value);
});



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

    document.getElementsByClassName('c-Dartboard')[0].append(hitMarker);
    // dartboardContainer.append(hitMarker);

    return hitMarker;
}

/**
 *
 *
 */
async function fetchData(gameId)
{
    // let gameId = document.getElementById('gameId').getAttribute('value');

    // return axios.get('/api/v1/user/showThrowsByGame/'+gameId).then( response =>
    // {
    //     const data = response.data.data.throws;
    //     _clearHitMarker();
    //     _clearHeatmap();
    //     renderHeatmap(data);
    //     updateRadarChart('graph01', data);
    //     renderPlot(data);

    // }).catch(function (error) {
    //     if (error.response) {
    //         console.log(error.response.data);
    //         console.error('Throws could not get fetched!');
    //     }
    // });

    return axios.get('/api/v1/dart/showThrows/'+gameId).then( response =>
    {
        const data = response.data.data.game;
        console.log(data);
        _clearHitMarker();
        _clearHeatmap();
        // renderHeatmap(data);
        // updateRadarChart('graph01', data);
        renderPlot(data);

    }).catch(function (error) {
        if (error.response) {
            console.log(error.response.data);
            console.error('Throws could not get fetched!');
        }
    });
}

/**
 *
 */
function initRadarChart(id)
{
    let plotData = [];
    for(let i = 0; i <= 360/20; i++)
        plotData.push(1);

    const data = [{
        r: plotData,
        theta: plotData.keys(),
        fill: 'toself',
        type: 'scatterpolar',
    }];

    const layout = {
        polar: {
          radialaxis: {
            visible: true,
            range: [0, Math.max(...plotData)*1.15],
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

    Plotly.newPlot(id, data, layout, config);
}

/**
 *
 */
function updateRadarChart(id, throwData = null)
{
    let plotData = [];
    for(let i = 0; i <= 360/20; i++)
        plotData.push(0);

    throwData.forEach( d => {
        var {distance, _, degrees} =  UTILS.cartesian2Polar(d.x * -1, d.y);
        let angle = UTILS.mod(90 - degrees, 360);

        for(const [i, c] of plotData.entries())
            if(i >= (angle/20)) {
                plotData[i-1]++;
                break;
            }
    });

    plotData = plotData.map( d => (d / plotData.length) * 100);
    const data = [{
        r: plotData,
        theta: plotData.keys(),
        fill: 'toself',
        type: 'scatterpolar',
    }];

    const layout = {
        polar: {
          radialaxis: {
            visible: true,
            range: [0, Math.max(...plotData)*1.15],
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

    Plotly.newPlot(id, data, layout, config);
    // Plotly.update(id, data);
}

/**
 *
 *
 */
function renderHeatmap(data)
{
    // var width = document.querySelector('#dartboardContainer').offsetWidth;
    // var height = document.querySelector('#dartboardContainer').offsetHeight;
    var width = document.getElementsByClassName('c-Dartboard')[0].offsetWidth;
    var height = document.getElementsByClassName('c-Dartboard')[0].offsetHeight;

    // if(width != height)
    //     console.warn('Something is wrong!');

    // console.log(width);
    // console.log(height);

    let widthHalf = width / 2;
    let heightHalf = height / 2;
    let multiplier = Math.min(widthHalf, heightHalf);

    let dartboardContainer = document.querySelector('#dartboardContainer');
    var points = [];

    data.forEach( w => {
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
        container: document.getElementsByClassName('c-Dartboard')[0],
        radius: 20,
    });

    let data1 = {
        max: 1,
        data: points
    };

    heatmapInstance.setData(data1);
}

function renderPlot(values)
{
    Object.values(groupByProperty(values.dart_throws, 'user_id')).forEach( (e, k) =>
    {
        console.log(e);
    });

    values = values.filter( (value, i) => !(value.sigma % 1) );

    const data = [{
        x: [...Array(values.length).keys()],
        y: values.map( value => value.value),
        type: 'scatter',
        mode: 'lines+markers',
        line: {shape: 'spline'},
    }];

    const layout = {
        title: 'Throws',
        xaxis: {
            title: 'Standard Deviation in mm',
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
            title: 'Expected score',
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
    }; // It's a stub, put layout config here.

    const config = {
        displayModeBar: false, // this is the line that hides the bar.
    };

    Plotly.newPlot('expectationDataGraph', data, layout, config);
}

function _clearHitMarker()
{
    let hitMarkers = document.querySelectorAll('.hitMarker');
    // console.log(hitMarkers);

    hitMarkers.forEach( marker => marker.remove() );
}

function _clearHeatmap()
{
    let hitMarkers = document.querySelectorAll('.heatmap-canvas');
    // console.log(hitMarkers);

    hitMarkers.forEach( marker => marker.remove() );
}
