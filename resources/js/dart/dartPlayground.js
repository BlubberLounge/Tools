/**
 * @author Maximilian Mewes
 *
 *
 */

import h337 from 'heatmap.js';
import Plotly from 'plotly.js-dist';

import Dartboard from './dartboard';
import * as UTILS from '../utils';


function createMatrix(size, value = 1)
{
    let result = [];
    for(let i = -size; i < size+1; i++) {
        let rows = [];
        for(let j = -size; j < size+1; j++)
            rows.push(value);

        result.push(rows);
    }

    return result;
}

$(function()
{
    fetchHeatmapData();

    let dartboard = new Dartboard('#dartboardContainer');
    dartboard.render();



    let size = 180;
    let matrix1 = [];
    for(let col = -size; col < size+1; col++) {
        let res = [];
        for(let row = -size; row < size+1; row++)
            res.push(getScore(row, col));

        matrix1.push(res);
    }


    let startSD = .5;
    let endSD = 99.5;

    for(let i = startSD; i <= endSD; i++) {
        calculate(i, size, matrix1);
    }

});

function calculate(standardDeviation, size, matrix)
{
    const start = performance.now();

    let weights = [];
    for(let i = -size; i < size+1; i++) {
        let res = [];
        for(let j = -size; j < size+1; j++) {
            let num1 = probability_density_normal_dist(i, 0, standardDeviation);
            let num2 = probability_density_normal_dist(j, 0, standardDeviation);
            res.push(
                    num1 * num2
            );
        }
        weights.push(res);
    }

    let resultMatrix = createMatrix(size, 0);
    let highest = 0;
    let matrixLength = matrix.length-1;
    let weightsLength = weights.length-1;
    let weightsLengthHalf = weightsLength / 2;
    for(let col=0; col <= matrixLength; col++) {
        for(let row=0; row <= matrixLength; row++) {
            var sum = 0;
            for(let i=0; i <= weightsLength; i++) {
                for(let j=0; j <= weightsLength; j++) {
                    let x = j + row - weightsLengthHalf;
                    let y = i + col - weightsLengthHalf;
                    if(x < 0 || y < 0 || x > matrixLength || y > matrixLength)
                        continue;
                    let points = weights[i][j] * matrix[y][x];
                    sum += points;
                }
            }
            resultMatrix[col][row] = sum;
            if(sum > highest)
                highest = sum;
        }
    }

    console.log('Result: ');
    console.log(`Standard Deviation: ${standardDeviation}mm`)
    console.log(`Highest Sum: ${highest}`);

    const end = performance.now();
    console.log(`Execution time: ${((end - start) / 1000 / 60)} minutes`);

    let data = {
        sigma: standardDeviation,
        expectedPoints: highest
    };

    axios.post('/api/v1/dart/expectationData', data).then( response => {
        console.warn('Successfuly stored data.');
    }).catch(function (error) {
        if (error.response) {
            console.log(error.response.data);
        }
    });
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

    let median = geometricMedian(points, 4);
    // console.table(median);
    placeMarker(dartboardContainer, median.x, median.y, 'median');

    let centroid = geometricCentroid(points);
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

/**
 *
 */
function probability_density_normal_dist (x, mu, sigma)
{
    var num = Math.exp(-Math.pow((x - mu), 2) / (2 * Math.pow(sigma, 2)))
    var denom = sigma * Math.sqrt(2 * Math.PI)
    return num / denom
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

/**
 *
 */
function renderPlot(mtx)
{
    let unpack = (rs, k) => rs.map(r => r[k] );

    var z_data=[ ]
    for(let i=0; i < mtx.length ;i++)
    {
        z_data.push( unpack(mtx, i) );
    }

    var data = [{
        z: z_data,
        type: 'surface'
    }];

    var layout = {
        title: 'DEBUG',
        autosize: false,
        width: 500,
        height: 500,
        margin: {
        l: 65,
        r: 50,
        b: 65,
        t: 90,
        }
    };

    Plotly.newPlot('myDiv', data, layout);
}

/**
 *
 */
function getScore(x, y)
{
    let fields = [1, 18, 4, 13, 6, 10, 15, 2, 17, 3, 19, 7, 16, 8, 11, 14, 9, 12, 5, 20];
    let rings = [0, 6.4, 16, 99, 107, 162, 170, Infinity];

    const radsToDegs = rad => rad * 180 / Math.PI;

    let theta = 0;
    let r = Math.sqrt(x*x + y*y);
    if (r != 0) {
        theta = Math.atan2(y,x);
        theta = radsToDegs(theta);
    } else {
        return 50;
    }

    let ring = rings.findIndex( ring => ring >= r )-1;

    let fieldSize = (360 / fields.length);
    let arr = [];
    for(let i = 0; i < fields.length; i++)
        arr.push(fieldSize * i);

    let phi = theta - fieldSize/2;
    phi = UTILS.mod(phi + 360, 360);

    let ind = arr.findIndex( f => f >= phi ) > 0 ? arr.findIndex( f => f >= phi ) : fields[fields.length-1] ;
    let field = fields[ind-1];

    let points = [
        50,
        25,
        field,
        field * 3,
        field,
        field * 2,
        0
    ];

    return points[ring];
}

/**
 *
 */
// function getScore(x, y)
// {
//     let fields = [1, 18, 4, 13, 6, 10, 15, 2, 17, 3, 19, 7, 16, 8, 11, 14, 9, 12, 5, 20];
//     let rings = [0, 6.4, 16, 99, 107, 162, 170, Infinity];

//     let theta = 0;
//     let r = Math.sqrt(x*x + y*y);
//     if (r != 0)
//         theta = Math.atan2(y,x);

//     // transform to zero is on the boundary of 20 and 1
//     // and the angle increases as we go clockwise
//     let phi = Math.PI / 2 - theta - Math.PI / 20;

//     let arr = [];
//     for(let i = 0; i < 20; i++)
//         arr.push((Math.PI/10) * i);

//     let field = fields[arr.findIndex( f => f >= UTILS.mod(phi, (2 * Math.PI)))];
//     let ring = rings.findIndex( ring => ring >= r );

//     let points = [
//         50,
//         25,
//         field,
//         field * 3,
//         field,
//         field * 2,
//         0
//     ];

//     return points[ring-1];
// }
