/**
 * @author Maximilian Mewes
 *
 *
 */

import DartSetup from './dart/dartSetup.js';

$(function()
{
    var dartSetup = new DartSetup();

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
            dartSetup.searchUser($(e.target).val());

    }, 400));
});
