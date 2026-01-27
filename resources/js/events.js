/**
 * @author Maximilian Mewes
 *
 * Timetable events page - uses Alpine.js for popovers
 */

import Timetable from "./timetable";

// Make timetable available globally for Alpine components
window.timetable = new Timetable('timetableTable');

// Close popovers when clicking outside
document.addEventListener('click', function (e) {
    // Get all open timetable popovers
    document.querySelectorAll('[data-timetable-popover]').forEach(function (popover) {
        const trigger = popover.previousElementSibling;
        if (trigger && !trigger.contains(e.target) && !popover.contains(e.target)) {
            // Dispatch close event
            popover.dispatchEvent(new CustomEvent('close-popover'));
        }
    });
});
