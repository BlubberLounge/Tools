// Notification popover is now handled via Alpine.js component in the blade template
// This file provides the notification loading and modal functionality

// Close notification popover when clicking outside
document.addEventListener('click', function (e) {
    const notificationPopover = document.getElementById('notification-popover');
    const notificationBtn = document.querySelector('[data-notification-toggle]');

    if (notificationPopover && notificationBtn) {
        if (!notificationPopover.contains(e.target) && !notificationBtn.contains(e.target)) {
            // Dispatch event to close the popover via Alpine
            notificationPopover.dispatchEvent(new CustomEvent('close-popover'));
        }
    }
});



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
            let borderColor = '';
            const hasAccepted = n.pivot.status === 'accepted';
            const hasDenied = n.pivot.status === 'denied';

            if(hasAccepted) {
                borderColor = 'border-success';
            } else if(hasDenied) {
                borderColor = 'border-danger';
            }

            userList += `<div class="w-1/2 md:w-auto mb-4">`+
                `<div class="flex justify-center">`+
                    `<img src="${n.img}" width="96" class="rounded-full border-4 ${borderColor} p-1">`+
                `</div>`+
                `<div class="text-center mt-2">`+
                    `${n.name}`+
                `</div>`+
            `</div>`;
        }

        const html = `
        <div x-data="{ open: true }" x-show="open" class="fixed inset-0 z-50" id="dartGameNotificationModal">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/50" @click="open = false; $el.parentElement.remove()"></div>

            <!-- Modal -->
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <div class="bg-base-100 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden" @click.stop>
                    <div class="flex items-center justify-between px-6 py-4 border-b border-base-300">
                        <h1 class="text-lg font-semibold">
                            ${notification.data.title}
                        </h1>
                        <button @click="open = false; $el.closest('#dartGameNotificationModal').remove()" class="text-zinc-500 hover:text-zinc-700">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="p-6">
                        <div class="text-center mb-4">
                            <h4 class="text-xl font-medium">
                                ${notification.game.title}
                            </h4>
                        </div>
                        <div class="flex flex-wrap justify-center gap-4">
                            ${userList}
                        </div>
                    </div>
                    <div class="flex items-center justify-around gap-2 px-6 py-4 border-t border-base-300">
                        <button type="button" id="btnModalDecline" class="btn btn-danger" @click="open = false; $el.closest('#dartGameNotificationModal').remove()"> Decline </button>
                        <button type="button" id="btnModalAccept" class="btn btn-success" @click="open = false; $el.closest('#dartGameNotificationModal').remove()"> Accept </button>
                    </div>
                </div>
            </div>
        </div>`;

        document.body.append(this.htmlToElement(html));

        // Re-initialize Alpine on the new element
        if (window.Alpine) {
            window.Alpine.initTree(document.getElementById('dartGameNotificationModal'));
        }

        document.getElementById('btnModalAccept').addEventListener('click', () => {
            const data = null;
            axios.put(`/api/v1/dart/${notification.game.id}/accept`, data).then( response => {

                axios.put(`/api/v1/notification/${notification.id}`, data).then ( r => {
                    const counter = document.getElementById('notification-counter');
                    if (counter) {
                        counter.innerHTML = parseInt(counter.innerHTML) - 1;
                    }
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
                    if (counter) {
                        counter.innerHTML = parseInt(counter.innerHTML) - 1;
                    }
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
