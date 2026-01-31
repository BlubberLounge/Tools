/**
 * @author Maximilian Mewes
 *
 *
 */

import DartSetup from './classes/dartSetup.js';

document.addEventListener('DOMContentLoaded', function()
{
    var dartSetup = new DartSetup();

    const debounce = (fn, delay = 1000) => {
        let timerId = null;
        return (...args) => {
            clearTimeout(timerId);
            timerId = setTimeout(() => fn(...args), delay);
        };
    };

    document.getElementById('SearchUser').addEventListener('input', debounce(function(e)
    {
        let input = e.target.value;

        if(input) {
            dartSetup.fetchUser(input);
        } else {
            document.getElementById('ListUser').innerHTML = ``;
        }
    }, 400));
});
