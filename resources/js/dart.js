/**
 * @author Maximilian Mewes
 *
 *
 */

import Dart from './dart/dart.js';

$(function()
{

    var dartGame = new Dart('#dartboard');
    $('#contact-tab').on('click', e =>
    {
        console.log('lick');
        dartGame.init();
    });

    document.querySelector('#dartboard').addEventListener('throw', function(d) {
        console.log(d.detail);
    });
});
