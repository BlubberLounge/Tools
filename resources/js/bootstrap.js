try {
    window.bootstrap = require('bootstrap');
    window.moment = require('moment');
    window.Popper = require('@popperjs/core');
} catch (e) {
    // console.log(e);
}

import 'animate.css';

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });

const myDefaultAllowList = bootstrap.Tooltip.Default.allowList

// To allow table elements
// myDefaultAllowList.table = []

// To allow td elements and data-bs-option attributes on td elements
// myDefaultAllowList.td = ['data-bs-option']

const BootstrapDataAttributes = /^data-bs-[\w-]+/
myDefaultAllowList['*'].push(BootstrapDataAttributes)

$('#sidebarCollapse').on('click', e =>
{
    $('#sidebar').toggleClass('active');
    // $('#sidebar').toggle();
});
