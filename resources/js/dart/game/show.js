/**
 * @author Maximilian Mewes
 *
 *
 */

import Dart from './classes/dart';
import NoSleep from 'nosleep.js';


$(function()
{
    var noSleepToggle = false;
    var noSleep = new NoSleep();
    // let clrRatio = new UTILS.CRGB(255, 255, 255).contrastRatio(new UTILS.CRGB(0, 90, 90));
    // console.log(clrRatio);

    var dartGame = new Dart('#dartboard');
    dartGame.init();

    document.querySelector('#keepOn').addEventListener('click', e =>
    {
        if(noSleepToggle) {
            noSleepToggle = false;
            noSleep.disable();
            console.warn('NoSleep disabled.');
            e.target.classList.remove('btn-successbtn-danger');
            e.target.classList.add('btn-danger');
        } else {
            noSleepToggle = true;
            noSleep.enable();
            console.warn('NoSleep enabled.');
            e.target.classList.remove('btn-danger');
            e.target.classList.add('btn-success');
        }
    });

});
