/**
 * @author Maximilian Mewes
 *
 *
 */

import Plotly from 'plotly.js-dist';
import h337 from 'heatmap.js';


$(function()
{
    // cool, ultra lightweight, but only supports click event
    // let confetti = new Confetti('confetti');
    // confetti.setCount(75);
    // confetti.setSize(1);
    // confetti.setPower(25);
    // confetti.setFade(false);
    // confetti.destroyTarget(false);

    fetchHeatmapData();

    podiumConfetti();
});

/**
 *
 *
 */
function podiumConfetti()
{
    var count = 300;
    var defaults = {
        origin: { y: .35 }
    };

    let fire = (particleRatio, opts) =>
    {
        confetti(Object.assign({}, defaults, opts, {
            particleCount: Math.floor(count * particleRatio)
        }));
    }

    fire(0.25, {
        spread: 26,
        startVelocity: 55,
    });

    fire(0.2, {
        spread: 60,
    });

    fire(0.35, {
        spread: 100,
        decay: 0.91,
        scalar: 0.8
    });

    fire(0.1, {
        spread: 120,
        startVelocity: 25,
        decay: 0.92,
        scalar: 1.2
    });

    fire(0.1, {
        spread: 120,
        startVelocity: 45,
    });
}

/**
 *
 *
 */
async function fetchHeatmapData()
{
    let gameId = document.getElementById('gameId').getAttribute('value');
    return await axios.get('/api/v1/dart/showThrows/'+gameId).then( response =>
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

    Object.values(groupByProperty(data.game.dart_throws, 'user_id')).forEach( (e, k) =>
    {
        var points = [];

        e.forEach( w => {
            points.push({
                x: Math.round( (width/2) - (w.x * 100) * -1 ),
                y: Math.round( (height/2) - (w.y * 100) ),
                value: .5
            });
        });
        // console.log(points);
        let i = k + 1;

        let skeletonHeatmap = document.getElementById('skeleton-heatmap'+i);
        if(skeletonHeatmap)
            skeletonHeatmap.remove();

        let heatmapInstance = h337.create({
            container: document.querySelector('.heatmap'+i),
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
