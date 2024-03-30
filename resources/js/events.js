/**
 * @author Maximilian Mewes
 *
 *
 */

import Timetable from "./timetable";

$(function()
{
    const timetable = new Timetable('timetableTable');

    const popoverTriggerList = $('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl, {
        html: true,
        title: function() {
            let date = moment(popoverTriggerEl.getAttribute('data-bl-timetable-date'));
            return 'Status ändern vom ' + date.format('DD.MM.YYYY');
        },
        content: function () {
            return timetable.getActions(popoverTriggerEl);
        }
      }));

    // Make sure only one popover is active at one time
    // [...popoverTriggerList].map(popoverTriggerEl => $(popoverTriggerEl).on('click', function (e)
    //     {
    //         $('.timetable-popover').not(this).popover('hide');
    //     }));

    //  Source:: https://stackoverflow.com/questions/11703093/how-to-dismiss-a-twitter-bootstrap-popover-by-clicking-outside
    $('body').on('click', function (e) {
        //     //did not click a popover toggle, or icon in popover toggle, or popover
        //     if ($(e.target).data('toggle') !== 'popover'
        //         && $(e.target).parents('[data-bs-toggle="popover"]').length === 0
        //         && $(e.target).parents('.popover.in').length === 0) {
        //         $('[data-bs-toggle="popover"]').popover('hide');
        //     }
        $('[data-bs-toggle="popover"]').each(function () {
            //the 'is' for buttons that trigger popups
            //the 'has' for icons within a button that triggers a popup
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                $(this).popover('hide');
            }
        });
    });
});
