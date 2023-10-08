require('./bootstrap');
require('./consoleText');

require('./sidebar');
require('./notification');

var audio = new Audio("/audio/example_audio.mp3");
audio.play();


const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl =>
    new bootstrap.Tooltip(tooltipTriggerEl, {
        html: true,
        offset: [0, 10],
        // title: function() {
        //     let date = moment(popoverTriggerEl.getAttribute('data-bl-timetable-date'));
        //     return 'Status ändern vom ' + date.format('DD.MM.YYYY');
        // },
        // content: function () {
        //     return timetable.getActions(popoverTriggerEl);
        // }
    })
);
