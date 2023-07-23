/**
 * @author Maximilian Mewes
 *
 *
 */

import * as UTILS from './utils';
import Dart from './dart/dart.js';

$(function()
{
    let clrRatio = new UTILS.CRGB(255, 255, 255).contrastRatio(new UTILS.CRGB(0, 90, 90));
    console.log(clrRatio);

    var dartGame = new Dart('#dartboard');

    const debounce = (fn, delay = 1000) => {
        let timerId = null;
        return (...args) => {
            clearTimeout(timerId);
            timerId = setTimeout(() => fn(...args), delay);
        };
    };

    $('#SearchUser').on('input', debounce(function(e)
    {
        let input = $(e.target).val();

        if(input)
            dartGame.searchUser($(e.target).val())
    }, 250));

    // $('.form-check-input').on('click', function()
    // {
    //     let id = $(this).val();
    //     if($(this).prop('checked')) {
    //         dartGame.addPlayer(id);
    //     } else {
    //         dartGame.removePlayer(id);
    //     }
    // });

    $('#contact-tab').on('click', e =>
    {
        dartGame.init();
    });

});
