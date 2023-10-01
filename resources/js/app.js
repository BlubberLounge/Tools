require('./bootstrap');
require('./consoleText');

require('./sidebar');

const notificationBtnTriggerList = document.querySelectorAll('[data-bs-toggle="notification"]')
const notificationBtnList = [...notificationBtnTriggerList].map(notificationBtnTriggerEl =>
    new bootstrap.Popover(notificationBtnTriggerEl, {
        html: true,
        offset: [-100, 5],
        placement: 'bottom',
        // content: function () {
        //     return notification.load();
        // }
    })
);

$('body').on('click', function (e) {
    $('[data-bs-toggle="notification"]').each(function () {
        if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0)
            $(this).popover('hide');
    });
});

[...notificationBtnTriggerList].map(notificationBtnTriggerEl =>
    notificationBtnTriggerEl.addEventListener('click', () => {
        const popover = bootstrap.Popover.getInstance(notificationBtnTriggerEl);
        notification.load(popover);

    })
);

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

const notification = {
    load(popover)
    {
        axios.get('/api/v1/notification').then( response => {

            const data = response.data.data;
            notification.update(popover, data.notifications);

        }).catch(function (error) {
            if (error.response) {
                console.log(error.response.data);
            }
        });
    },

    update(popover, notifications)
    {
        let content = '';

        if(typeof notifications !== 'undefined' && notifications.length !== 0) {
            for(const [i, n] of notifications.entries()) {
                const title = n.data.title ?? 'no title found';
                const time = moment().diff(moment(n.created_at), 'days') < 7 ? moment(n.created_at).fromNow() : moment(n.created_at).format('DD.MM.YYYY hh:mm:ss');
                let icon = '<i class="fa-solid fa-exclamation fa-xl text-info"></i>';

                if(n.data.level == 2) {
                    icon = '<i class="fa-solid fa-circle-exclamation fa-xl text-warning"></i>';
                } else if(n.data.level == 3) {
                    icon = '<i class="fa-solid fa-triangle-exclamation fa-xl text-danger"></i>';
                }

                // let btns = '';

                // if(n.type == 'DartGameStarted') {
                //     btns += '<a class="btn btn-primary" href="#" role="button">Link</a>';
                //     console.log('a');
                // }

                content += `<a href="#" class="notification-item row align-items-center p-0 py-2" data-bs-toggle="modal" data-bs-target="#staticBackdrop">`+
                                `<div class="col-1 d-flex justify-center">`+
                                    `${ icon }`+
                                `</div>`+
                                `<div class="col">`+
                                    `<div class="row">`+
                                        `${ title }`+
                                    `</div>`+
                                    `<div class="row">`+
                                        `${ time }`+
                                    `</div>`+
                                `</div>`+
                                // `<div class="col">`+
                                //     `${ btns }`+
                                // `</div>`+
                            `</a>`+
                            `${(i < notifications.length-1 ? `<hr class="my-1" />` : '')}`;
            }

            let modal = `
            <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Modal title</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        ...
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Understood</button>
                    </div>
                    </div>
                </div>
            </div>`;



            document.body.append(htmlToElement(modal));
        } else {
            content +=
                `<div class="d-flex flex-column justify-center align-center text-center notification-no-container">`+
                    `<i class="fa-regular fa-bell fa-9x mb-4"></i>`+
                    `<div class="text-muted fw-medium notification-text">`+
                        `Hier findest du deine <br /> Benachrichtigungen`+
                    `</div>`+
                `</div>`;
        }

        popover.setContent({
            '.popover-header': popover._config.title,
            '.popover-body': content,
        });

        const notificationItemList = document.querySelectorAll('.notification-item');
        [...notificationItemList].map( item => {
            item.addEventListener('click', function() {
                console.log('clicked');
                popover.hide();
            });
        });
    }
};

function htmlToElement(html) {
    var template = document.createElement('template');
    template.innerHTML = html.trim();
    return template.content.firstChild;
}
