/**
 * @author Maximilian Mewes
 *
 *
 */

import h337 from 'heatmap.js';
import Dartboard from './dart/dartboard';


$(function()
{
    fetchHeatmapData();
    let dartboards = [];

    document.querySelectorAll('.heatmap').forEach( (e, i) =>
    {
        let dartboard = new Dartboard(`#heatmap${i+1}`);
        dartboard.render();

        dartboards.push(dartboard);

    });
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
    return axios.get('/api/v1/dart/showThrows/'+gameId).then( response =>
    {
        // console.log(response.data.data);
        renderHeatmaps(response.data.data);

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
function renderHeatmaps(data)
{
    var width = document.querySelector('.heatmap1').offsetWidth;
    var height = document.querySelector('.heatmap1').offsetHeight;

    // if(width != height)
    //     console.warn('Something is wrong!');

    // console.log(width);
    // console.log(height);

    let widthHalf = width / 2;
    let heightHalf = height / 2;
    let multiplier = Math.min(widthHalf, heightHalf);

    Object.values(groupByProperty(data.game.dart_throws, 'user_id')).forEach( (e, k) =>
    {
        let dartboardContainer = document.querySelector(`.heatmap${k+1}`);
        var points = [];

        e.forEach( w => {
            points.push({
                x: Math.round( widthHalf - (w.x * multiplier) * -1 ),
                y: Math.round( heightHalf - (w.y * multiplier) ),
                value: .5
            });
        });
        // console.table(points);

        let median = geometricMedian(points, 4);
        // console.table(median);
        placeMarker(dartboardContainer, median.x, median.y, 'median');

        let centroid = geometricCentroid(points);
        // console.table(centroid);
        placeMarker(dartboardContainer, centroid.x, centroid.y, 'centroid');

        let skeletonHeatmap = document.getElementById(`skeleton-heatmap${k+1}`);
        if(skeletonHeatmap)
            skeletonHeatmap.remove();

        let heatmapInstance = h337.create({
            container: dartboardContainer,
            radius: 20,
        });
        let data = {
            max: 1,
            data: points
        };
        heatmapInstance.setData(data);
    });
}

/**
 *
 *
 */
function groupByProperty(xs, key)
{
    return xs.reduce((rv, x) => {
        (rv[x[key]] = rv[x[key]] || []).push(x);
        return rv;
    }, {});
}

/**
 *
 *
 */
function geometricCentroid(points)
{
    let xSum = 0;
    let ySum = 0;

    points.forEach( point =>
    {
        xSum += point.x;
        ySum += point.y;
    });

    return {x: xSum / points.length, y: ySum / points.length};
}

/**
 *
 *
 */
function geometricMedian(arr, n)
{
    // Current x coordinate and y coordinate
    let current_point = {x:0, y:0};

    let test_point = [{x:-1, y:0}, {x:0, y:1}, {x:1, y:0}, {x:0, y:-1}];
    let lower_limit = 0.01;

    for (let i = 0; i < n; i++) {
        current_point.x = current_point.x + arr[i].x;
        current_point.y = current_point.y + arr[i].y;
    }

    // Here current_point becomes the
    // Geographic MidPoint
    // Or Center of Gravity of equal
    // discrete mass distributions
    current_point.x /= n;
    current_point.y /= n;

    // minimum_distance becomes sum of
    // all distances from MidPoint to
    // all given points
    let minimum_distance = distSum(current_point, arr, n);

    let k = 0;
    while (k < n) {
        for (let i = 0; i < n, i != k; i++) {
            let newpoint = {x:0, y:0};
            newpoint.x = arr[i].x;
            newpoint.y = arr[i].y;
            let newd = distSum(newpoint, arr, n);
            if (newd < minimum_distance) {
                minimum_distance = newd;
                current_point.x = newpoint.x;
                current_point.y = newpoint.y;
            }
        }
        k++;
    }

    // Assume test_distance to be 1000
    let test_distance = 1000;
    let flag = 0;

    // Test loop for approximation starts here
    while (test_distance > lower_limit) {

        flag = 0;

        // Loop for iterating over all 4 neighbours
        for (let i = 0; i < 4; i++) {

            // Finding Neighbours done
            let newpoint = {};
            newpoint.x = current_point.x + test_distance * test_point[i].x;
            newpoint.y = current_point.y + test_distance * test_point[i].y;

            // New sum of Euclidean distances
            // from the neighbor to the given
            // data points
            let newd = distSum(newpoint, arr, n);

            if (newd < minimum_distance) {

                // Approximating and changing
                // current_point
                minimum_distance = newd;
                current_point.x = newpoint.x;
                current_point.y = newpoint.y;
                flag = 1;
                break;
            }
        }

        // This means none of the 4 neighbours
        // has the new minimum distance, hence
        // we divide by 2 and reiterate while
        // loop for better approximation
        if (flag == 0)
            test_distance /= 2;
    }

    // console.log("Geometric Median = (", current_point.x, ", ", current_point.y, ") with minimum distance = ", minimum_distance.toFixed(5));
    return { x:current_point.x, y:current_point.y };
}

/**
 *
 *
 */
function distSum(p, arr, n)
{
    let sum = 0;
    for (let i = 0; i < n; i++) {
        let distX = Math.abs(arr[i].x - p.x);
        let distY = Math.abs(arr[i].y - p.y);
        sum += Math.sqrt((distX * distX) + (distY * distY));
    }
    return sum;
}
