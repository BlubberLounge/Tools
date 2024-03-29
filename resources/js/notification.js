
$('body').on('click', function (e) {
    $('[data-bs-toggle="notification"]').each(function () {
        if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0)
            $(this).popover('hide');
    });
});

const notificationBtnTriggerList = document.querySelectorAll('[data-bs-toggle="notification"]')
const notificationBtnList = [...notificationBtnTriggerList].map(notificationBtnTriggerEl =>
    new bootstrap.Popover(notificationBtnTriggerEl, {
        html: true,
        offset: [-100, 5],
        placement: 'bottom',
        customClass: 'popover-notification',
        content: function () {
            return `<div class="d-flex flex-column justify-center align-items-center notification-no-container">`+
                `<div class="spinner-grow text-secondary mb-3"></div>`+
                `<div class="text-muted fw-medium notification-text">`+
                    `Hier findest du deine <br /> Benachrichtigungen`+
                `</div>`+
            `</div>`;
        }
    })
);

[...notificationBtnTriggerList].map(notificationBtnTriggerEl =>
    notificationBtnTriggerEl.addEventListener('click', () => {
        const popover = bootstrap.Popover.getInstance(notificationBtnTriggerEl);
        notification.load(popover);
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
        let counter = document.getElementById('notification-counter');

        if(typeof notifications !== 'undefined' && notifications.length !== 0) {
            if(counter)
                counter.innerHTML = notifications.length;

            for(const [i, n] of notifications.entries()) {
                const title = n.data.title ?? 'no title found';
                const time = moment().diff(moment(n.created_at), 'days') < 7 ? moment(n.created_at).fromNow() : moment(n.created_at).format('DD.MM.YYYY hh:mm:ss');
                let icon = `<i class="fa-solid fa-fire fa-xl text-fire"></i>`;

                if(n.data.level == 2) {
                    icon = '<i class="fa-solid fa-circle-exclamation fa-xl text-warning"></i>';
                } else if(n.data.level == 3) {
                    icon = '<i class="fa-solid fa-triangle-exclamation fa-xl text-danger"></i>';
                }

                if(n.type == 'UserRegisteredNotification') {
                    content += `<div class="notification-item notification-ok row align-items-center p-0 py-2" data-bl-id="${ n.id }">`+
                        `<div class="col-2 d-flex justify-center">`+
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
                        `<div class="col-2 d-flex justify-center">`+
                            `<a href="#" class="btn btn-mark-as-read">`+
                                `<i class="fa-solid fa-check"></i>`+
                            `</a>`+
                        `</div>`+
                    `</div>`+
                    `${(i < notifications.length-1 ? `<hr class="my-1" />` : '')}`;
                } else {
                    content += `<a href="#" class="notification-item notification-modal row align-items-center p-0 py-2" data-bl-id="${ n.id }">`+
                        `<div class="col-2 d-flex justify-center">`+
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
                    `</a>`+
                    `${(i < notifications.length-1 ? `<hr class="my-1" />` : '')}`;
                }
            }
        } else {
            if(counter)
                counter.remove();

            content +=
                `<div class="d-flex flex-column justify-center align-items-center notification-no-container">`+
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

        const notificationItemOkList = document.querySelectorAll('.notification-ok');
        [...notificationItemOkList].map( item => {
            item.getElementsByClassName('btn-mark-as-read')[0].addEventListener('click', e => {
                const id = item.getAttribute('data-bl-id');
                axios.put(`/api/v1/notification/${id}`, null).then ( r => {

                    const notificationItem = e.target.closest('.notification-item');

                    if(notificationItem.previousSibling) {
                        notificationItem.previousSibling.remove();
                    } else if(notificationItem.nextSibling) {
                        notificationItem.nextSibling.remove();
                    }

                    notificationItem.remove();

                    const counter = document.getElementById('notification-counter');
                    counter.innerHTML = counter.innerHTML - 1;
                }).catch(function (error) {
                    if (error.response) {
                        console.log(error.response.data);
                    }
                });
            });
        });

        const notificationItemModalList = document.querySelectorAll('.notification-modal');
        [...notificationItemModalList].map( item => {
            item.addEventListener('click', e => {
                const id = item.getAttribute('data-bl-id');
                modal.load(id);

                popover.hide();
            });
        });
    }
};

const modal = {
    load(id)
    {
        axios.get(`/api/v1/notification/${id}`).then( response => {

            const notification = response.data.data.notification;
            this.addToDOM(notification);

            const myModal = new bootstrap.Modal('#dartGameNotificationModal');
            myModal.show();

            const myModalEl = document.getElementById('dartGameNotificationModal');
            myModalEl.addEventListener('hidden.bs.modal', event =>
            {
                myModalEl.remove(); // cleanup
            });

        }).catch(function (error) {
            if (error.response) {
                console.log(error.response.data);
            }
        });
    },

    addToDOM(notification)
    {
        let userList = '';

        for(const [i, n] of notification.game.users.entries()) {
            let borderColor = null;
            const hasAccepted = n.pivot.status === 'accepted';
            const hasDenied = n.pivot.status === 'denied';

            if(hasAccepted) {
                borderColor = 'border-success';
            } else if(hasDenied) {
                borderColor = 'border-danger';
            } else {
                //
            }

            userList += `<div class="col-6 col-md mb-4">`+
                `<div class="row justify-center">`+
                    `<div class="col-auto">`+
                        `<img src="${n.img}" width="96" class="rounded-circle border border-3 ${borderColor} p-1">`+
                    `</div>`+
                `</div>`+
                `<div class="row text-center">`+
                    `<div class="col">`+
                        `${n.name}`+
                    `</div>`+
                `</div>`+
            `</div>`;
        }

        const html = `
        <div class="modal fade" id="dartGameNotificationModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">
                            ${notification.data.title}
                        </h1>
                    </div>
                    <div class="modal-body">
                        <div class="row text-center mb-4">
                            <div class="col">
                                <h4>
                                    ${notification.game.title}
                                </h4>
                            </div>
                        </div>
                        <div class="row justify-center">
                            ${userList}
                        </div>
                    </div>
                    <div class="modal-footer justify-content-around">
                        <button type="button" id="btnModalDecline" class="btn btn-danger" data-bs-dismiss="modal"> Decline </button>
                        <button type="button" id="btnModalAccept" class="btn btn-success" data-bs-dismiss="modal"> Accept </button>
                    </div>
                </div>
            </div>
        </div>`;

        document.body.append(this.htmlToElement(html));

        document.getElementById('btnModalAccept').addEventListener('click', () => {
            const data = null;
            axios.put(`/api/v1/dart/${notification.game.id}/accept`, data).then( response => {

                axios.put(`/api/v1/notification/${notification.id}`, data).then ( r => {
                    const counter = document.getElementById('notification-counter');
                    counter.innerHTML = counter.innerHTML - 1;
                }).catch(function (error) {
                    if (error.response) {
                        console.log(error.response.data);
                    }
                });

            }).catch(function (error) {
                if (error.response) {
                    console.log(error.response.data);
                }
            });
        });

        document.getElementById('btnModalDecline').addEventListener('click', () => {
            const data = null;
            axios.put(`/api/v1/dart/${notification.game.id}/decline`, data).then( response => {

                axios.put(`/api/v1/notification/${notification.id}`, data).then ( r => {
                    const counter = document.getElementById('notification-counter');
                    counter.innerHTML = counter.innerHTML - 1;
                }).catch(function (error) {
                    if (error.response) {
                        console.log(error.response.data);
                    }
                });

            }).catch(function (error) {
                if (error.response) {
                    console.log(error.response.data);
                }
            });
        });
    },

    htmlToElement(html) {
        var template = document.createElement('template');
        template.innerHTML = html.trim();
        return template.content.firstChild;
    }
};

// funktioniert nicht auf mobil geräten
// Notification.requestPermission().then( perm => {
//     if(perm === "granted") {
//         new Notification("Hello from the other side!");
//     }
// })

// console.log('a');
// navigator.serviceWorker.register('js/s.js');
// console.log('b');
// Notification.requestPermission(function(result) {
//   if (result === 'granted') {
//     navigator.serviceWorker.ready.then(function(registration) {
//       registration.showNotification('Notification with ServiceWorker');
//     });
//   }
// });
// console.log('c');

function initSW() {
    if (!"serviceWorker" in navigator)
        return;

    if (!"PushManager" in window)
        return;

    //register the service worker
    navigator.serviceWorker.register('/sw.js').then(() => {
        console.log('serviceWorker installed!')
        initPush();
    }).catch((err) => {
        console.log(err)
    });
}

initSW();

function initPush() {
    if (!navigator.serviceWorker.ready) {
        return;
    }

    new Promise(function (resolve, reject) {
        const permissionResult = Notification.requestPermission(function (result) {
            resolve(result);
        });

        if (permissionResult) {
            permissionResult.then(resolve, reject);
        }
    })
    .then((permissionResult) => {
        if (permissionResult !== 'granted') {
            throw new Error('We weren\'t granted permission.');
        }
        subscribeUser();
    });
}


function subscribeUser() {
    navigator.serviceWorker.ready.then((registration) => {
            const subscribeOptions = {
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(
                    'BKB8E5bWuwg3zsbUxi-lT65RkABmQKFp2r8XM9Z_MlYU1-8y-RVaqxrv44GYJO3cO0aaHVufkIksglHzFlOyYUA'
                )
            };

            return registration.pushManager.subscribe(subscribeOptions);
        })
        .then((pushSubscription) => {
            console.log('Received PushSubscription: ', JSON.stringify(pushSubscription));
            storePushSubscription(pushSubscription);
        });
}

function urlBase64ToUint8Array(base64String) {
    var padding = '='.repeat((4 - base64String.length % 4) % 4);
    var base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    var rawData = window.atob(base64);
    var outputArray = new Uint8Array(rawData.length);

    for (var i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

function storePushSubscription(pushSubscription) {
    const token = document.querySelector('meta[name=csrf-token]').getAttribute('content');

    fetch('/api/v1/notification/push', {
        method: 'POST',
        body: JSON.stringify(pushSubscription),
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-Token': token
        }
    })
        .then((res) => {
            return res.json();
        })
        .then((res) => {
            console.log(res)
        })
        .catch((err) => {
            console.log(err)
        });
}
