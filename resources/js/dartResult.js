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

    var count = 300;
    var defaults = {
      origin: { y: .35 }
    };

    function fire(particleRatio, opts) {
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

    let result = $.ajax({
        url: '/api/v1/dart/showThrows/99dd52b1-e52c-4a33-b7df-911f086de799',
        type: 'GET',
        async: false,
        success: function (data)
        {
            return data.data;
        },
        error: function (xhr, exception) {
            console.error(msg);
            return null;
        }
    });
    let d = result.responseJSON.data;

    var groupBy = function(xs, key)
    {
        return xs.reduce(function(rv, x) {
          (rv[x[key]] = rv[x[key]] || []).push(x);
          return rv;
        }, {});
    };

    // console.log(groupBy(d.game.dart_throws, 'user_id'));
    var width = document.querySelector('.heatmap1').offsetWidth;
    var height = document.querySelector('.heatmap1').offsetHeight;

    Object.values(groupBy(d.game.dart_throws, 'user_id')).forEach( (e, k) =>
    {
        var points = [];

        e.forEach( w => {
            points.push({
                x: Math.round( (width/2) - (w.x * 100) * -1 ),
                y: Math.round( (height/2) - (w.y * 100) ),
                value: .5
            });
        });

        console.log(points);

        let i = k + 1;
        let heatmapInstance = h337.create({
            container: document.querySelector('.heatmap'+i),
            radius: 20,
            maxOpacity : 1,
            minOpacity: 0,
        });
        let data = {
            max: 1,
            data: points
        };
        heatmapInstance.setData(data);

    });
});

