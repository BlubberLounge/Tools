/**
 * @author Maximilian Mewes
 *
 *
 */

import * as UTILS from './utils';
import Dart from './dart/dart.js';

$(function()
{
    // let clrRatio = new UTILS.CRGB(255, 255, 255).contrastRatio(new UTILS.CRGB(0, 90, 90));
    // console.log(clrRatio);

    var dartGame = new Dart('#dartboard');
    dartGame.init();

});
