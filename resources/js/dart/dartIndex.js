/**
 * @author Maximilian Mewes
 *
 *
 */

import h337 from 'heatmap.js';
import Plotly from 'plotly.js-dist';

import * as UTILS from '../utils';
import { baseConfig, baseLayout } from './dartPlotUtils';
import Dartboard from './dartboard';
import DartCalculator from './dartCalculator';

$(function()
{
    const dartboard = new Dartboard('#dartboardContainer', {size: 380});
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
    return axios.get('/api/v1/dart/showThrows/'+gameId).then( response =>
    {
        const dataGame = response.data.data.game;
        const currentUser = response.data.data.user;

        _clearAll();
        renderHeatmap(dataGame, currentUser);
        // updateRadarChart('graph01', data);
        renderExpectedScoreChart(dataGame, currentUser);

    }).catch(function (error) {
        if (error.response) {
            console.log(error.response.data);
            console.error('Data could not get fetched!');
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

    var layout = baseLayout;
    layout.polar.radialaxis.range = [0, Math.max(...plotData)*1.15];

    Plotly.newPlot(id, data, layout, baseConfig);
}

/**
 *
 */
function updateRadarChart(id, throwData = null, user)
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

    var layout = baseLayout;
    layout.polar.radialaxis.range = [0, Math.max(...plotData)*1.15];

    Plotly.newPlot(id, data, layout, baseConfig);
    // Plotly.update(id, data);
}

function groupByProperty(xs, key)
{
    return xs.reduce((rv, x) => {
        (rv[x[key]] = rv[x[key]] || []).push(x);
        return rv;
    }, {});
}

function renderExpectedScoreChart(values, currentUser)
{
    var data = [];
    Object.values(groupByProperty(values.dart_throws, 'user_id')).forEach( (e, k) =>
    {
        let user = values.users.find( a => a.id == e[0].user_id);
        let add = user.id == currentUser.id ? '(ich)' : '';
        data.push({
            x: [...Array(e.length).keys()],
            y: e.map( e => e.value),
            type: 'scatter',
            mode: 'lines+markers',
            line: {shape: 'spline'},
            // line: {shape: 'linear'},
            name: `${user.firstname} ${user.lastname} ${add}`
        });

        let sum = e.reduce((total, t) => total + t.value, 0);
        let avg = sum / e.length;

        data.push({
            x: [0, e.length-1],
            y: [avg, avg],
            type: 'scatter',
            mode: 'lines',
            line: {
                width: 2,
                dash: 'dash'
            },
            // line: {shape: 'linear'},
            name: `AVG ${user.firstname} ${user.lastname}`
        });
    });

    var layout = baseLayout;
    layout.title = 'Erwartete Punkte';
    layout.xaxis.range = [0, 20];

    Plotly.newPlot('expectationDataGraph', data, layout, baseConfig);
}


/**
 *
 *
 */
function renderHeatmap(data, user)
{
    var data = data.dart_throws.filter( d => d.user_id === user.id );
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

function _clearAll()
{
    _clearHeatmap();
    _clearHitMarker();
}

function _clearHitMarker()
{
    let hitMarkers = document.querySelectorAll('.hitMarker');

    hitMarkers.forEach( marker => marker.remove() );
}

function _clearHeatmap()
{
    let hitMarkers = document.querySelectorAll('.heatmap-canvas');

    hitMarkers.forEach( marker => marker.remove() );
}
