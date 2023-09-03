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

    const size = 180;
    const startSD = 10;
    const endSD = 11;

    for(let i = startSD; i <= endSD; i+=.5)
        Playground.calculate(i, size);

});

class Playground
{
    static dartboardMatrix = this.generateDartboardMatrix(180);
    static strategyBestTargetCalculator = null;

    static calculate(standardDeviation, size)
    {
        // this.strategyBestTargetCalculator = new CalculateBestTargetV1();
        this.strategyBestTargetCalculator = new CalculateBestTargetSymmetricKernel();

        const start = performance.now();

        let data = this.strategyBestTargetCalculator.calculate(standardDeviation, size, this.dartboardMatrix);

        const end = performance.now();
        console.warn('Result: ');
        console.log(`[Playground] Execution time: ${((end - start) / 1000)} sec.`);
        console.log(`Standard Deviation: ${standardDeviation}mm`)
        console.log(`Highest: `);
        console.table({
            sigma: data.sigma,
            point: data.expectedPoint
        });

        // this.saveData(data);
    }

    static generateDartboardMatrix(size)
    {
        let matrix = [];
        for(let col = -size; col < size+1; col++) {
            let res = [];
            for(let row = -size; row < size+1; row++)
                res.push(getScore(row, col));

            matrix.push(res);
        }

        return matrix;
    }

    static saveData(data)
    {
        axios.post('/api/v1/dart/expectationData', data).then( response => {

            console.warn('Successfuly stored data.');

        }).catch(function (error) {
            if (error.response) {
                console.log(error.response.data);
            }
        });
    }
};

class CalculateBestTargetV1
{
    constructor()
    {

    }

    probability_density_normal_dist(x, mu, sigma)
    {
        var num = Math.exp(-Math.pow((x - mu), 2) / (2 * Math.pow(sigma, 2)))
        var denom = sigma * Math.sqrt(2 * Math.PI)
        return num / denom
    }

    calculate(standardDeviation, size, dartboardMatrix)
    {
        let kernel = [];
        for(let i = -size; i < size+1; i++) {
            let res = [];
            for(let j = -size; j < size+1; j++) {
                let num1 = this.probability_density_normal_dist(i, 0, standardDeviation);
                let num2 = this.probability_density_normal_dist(j, 0, standardDeviation);
                res.push(
                        num1 * num2
                );
            }
            kernel.push(res);
        }

        let resultMatrix = [...Array(size*2+1)].map(e => Array(size*2+1).fill(0));
        let highestPoint = {
            score: 0,
            x: 0,
            y: 0,
        };
        const matrixLength = dartboardMatrix.length-1;
        const kernelLength = kernel.length-1;
        const kernelLengthHalf = kernelLength / 2;

        for(let col=0; col <= matrixLength; col++)
            // console.log('.');
            for(let row=0; row <= matrixLength; row++) {
                if(dartboardMatrix[col][row] <= 0)
                    continue;
                var sum = 0;
                for(let i=0; i <= kernelLength; i++) {
                    for(let j=0; j <= kernelLength; j++) {
                        let x = j + row - kernelLengthHalf;
                        let y = i + col -kernelLengthHalf;
                        if(x < 0 || y < 0 || x > matrixLength || y > matrixLength || dartboardMatrix[y][x] <= 0)
                            continue;

                        sum += kernel[i][j] * dartboardMatrix[y][x];
                    }
                }
                resultMatrix[col][row] = sum;
                if(sum > highestPoint.score)
                    highestPoint = {
                        score: sum,
                        x: col,
                        y: row,
                    };
            }

       return {
            sigma: standardDeviation,
            expectedPoint: {
                score: highestPoint.score,
                x: (highestPoint.x - kernelLengthHalf) / dartboardMatrix.length,
                y: (highestPoint.y - kernelLengthHalf) / dartboardMatrix.length,
            },
            version: 200,
        };
    }
}

class CalculateBestTargetSymmetricKernel
{
    constructor()
    {

    }

    probability_density_normal_dist(x, mu, sigma)
    {
        var num = Math.exp(-Math.pow((x - mu), 2) / (2 * Math.pow(sigma, 2)))
        var denom = sigma * Math.sqrt(2 * Math.PI)
        return num / denom
    }

    calculate(standardDeviation, size, matrix)
    {
        let kernel = [];
        for(let i = -size; i < size+1; i++)
            kernel.push(this.probability_density_normal_dist(i, 0, standardDeviation));

        let resultMatrix = [...Array(size*2+1)].map(e => Array(size*2+1).fill(0));
        let matrixLength = matrix.length-1;
        let kernelLength = kernel.length-1;
        let kernelLengthHalf = kernelLength / 2;
        let highestPoint = {
            score: 0,
            x: 0,
            y: 0,
        };

        let interMatrix = [...Array(size*2+1)].map(e => Array(size*2+1).fill(0));
        for(let col=0; col <= matrixLength; col++)
            for(let row=0; row <= matrixLength; row++) {
                if(matrix[col][row] <= 0)
                    continue;
                var sum = 0;
                for(let i=0; i <= kernelLength; i++) {
                    let x = i + row - kernelLengthHalf;
                    if(x < 0 || x > kernelLength)
                        continue;
                    sum += matrix[col][x] * kernel[i];
                }
                interMatrix[col][row] = sum;
            }

        for(let col=0; col <= matrixLength; col++)
            for(let row=0; row <= matrixLength; row++) {
                if(interMatrix[col][row] <= 0)
                    continue;
                var sum = 0;
                for(let i=0; i <= kernelLength; i++) {
                    let y = i + col - kernelLengthHalf;
                    if(y < 0 || y > kernelLength)
                        continue;
                    sum += interMatrix[y][row] * kernel[i];
                }
                resultMatrix[col][row] = sum;
                if(sum > highestPoint.score)
                    highestPoint = {
                        score: sum,
                        x: col,
                        y: row,
                    };
            }

        return {
            sigma: standardDeviation,
            expectedPoint: {
                score: highestPoint.score,
                x: (highestPoint.x - kernelLengthHalf) / matrix.length,
                y: (highestPoint.y - kernelLengthHalf) / matrix.length,
            },
            version: 200,
        };
    }
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
    const width = document.querySelector('#dartboardContainer').offsetWidth;
    const height = document.querySelector('#dartboardContainer').offsetHeight;

    // if(width != height)
    //     console.warn('Something is wrong!');

    // console.log(width);
    // console.log(height);

    const widthHalf = width / 2;
    const heightHalf = height / 2;
    const multiplier = Math.min(widthHalf, heightHalf);

    const dartboardContainer = document.querySelector('#dartboardContainer');
    var points = [];

    data.throws.forEach( w => {
        points.push({
            x: Math.round( widthHalf - (w.x * multiplier) * -1 ),
            y: Math.round( heightHalf - (w.y * multiplier) ),
            value: .5
        });
    });
    // console.table(points);

    const median = DartCalculator.calculateGeometricMedian(points, 4);
    // console.table(median);
    placeMarker(dartboardContainer, median.x, median.y, 'median');

    const centroid = DartCalculator.calculateGeometricCentroid(points);
    // console.table(centroid);
    placeMarker(dartboardContainer, centroid.x, centroid.y, 'centroid');

    const skeletonHeatmap = document.getElementById('skeleton-dartboard');
    if(skeletonHeatmap)
        skeletonHeatmap.remove();

    let heatmapInstance = h337.create({
        container: dartboardContainer,
        radius: 20,
    });

    const data1 = {
        max: 1,
        data: points
    };

    heatmapInstance.setData(data1);
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
    const fields = [1, 18, 4, 13, 6, 10, 15, 2, 17, 3, 19, 7, 16, 8, 11, 14, 9, 12, 5, 20];
    const rings = [0, 6.4, 16, 99, 107, 162, 170, Infinity];

    const radsToDegs = rad => rad * 180 / Math.PI;

    let theta = 0;
    const r = Math.sqrt(x*x + y*y);
    if (r != 0) {
        theta = Math.atan2(y,x);
        theta = radsToDegs(theta);
    } else {
        return 50;
    }

    const ring = rings.findIndex( ring => ring >= r )-1;

    const fieldSize = (360 / fields.length);
    let arr = [];
    for(let i = 0; i < fields.length; i++)
        arr.push(fieldSize * i);

    let phi = theta - fieldSize/2;
    phi = UTILS.mod(phi + 360, 360);

    let ind = arr.findIndex( f => f >= phi ) > 0 ? arr.findIndex( f => f >= phi ) : fields[fields.length-1] ;
    const field = fields[ind-1];

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
