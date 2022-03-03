window.Pusher = require('pusher-js');

window.Popper = require('popper.js').default;

window.Push = require('push.js');

window.Cookies = require('js-cookie');

window.TWEEN = require('tween.js');

window.DigaVersion = VERSION;

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    let jq = require('jquery');
    window.$ = jq;
    window.jQuery = jq;

    require('bootstrap');

    require("../../../node_modules/pc-bootstrap4-datetimepicker/build/js/bootstrap-datetimepicker.min.js");

    require('bootstrap-tour');

    require("vue2vis/dist/vue2vis.css");

    // require("vue2-datepicker/index.css");
    


} catch (e) {
    console.log(e);
}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Vue-resource
 * */

var vueResource = require('vue-resource');
Vue.use(vueResource);

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */
// Disabled, because of REST API, see:
// https://security.stackexchange.com/questions/166724/should-i-use-csrf-protection-on-rest-api-endpoints
//
// let token = document.head.querySelector('meta[name="csrf-token"]');
//
// if (token) {
//     // axios
//     window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
//     // vue-resource
//     Vue.http.headers.common['X-CSRF-TOKEN'] = token.content;
// } else {
//     console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
// }

import Echo from 'laravel-echo'

if (process.env.NODE_ENV !== 'production') Pusher.logToConsole = true;

window.initialize_bearer = function(bearer) { // this function executes as well in auth.js
    // axios
    window.axios.defaults.headers.common['Authorization'] = bearer;
    // vue-resource
    Vue.http.headers.common['Authorization'] = bearer;
    // bearer also used in vue-core-image-upload component

    /**
     * Echo exposes an expressive API for subscribing to channels and listening
     * for events that are broadcast by Laravel. Echo and event broadcasting
     * allows your team to easily build robust real-time web applications.
     */

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: process.env.MIX_PUSHER_KEY,
        cluster: 'eu',
        encrypted: true,
        auth: {
            headers: {
                Authorization: bearer,
            },
        },
    });
};

let bearer = Cookies.get('access_token');

if (bearer) {
    initialize_bearer(bearer);
}

import store from './vuex/store';
/**
 * Vue resource interceptor
 */
Vue.http.interceptors.push(function(request, next) {
    // continue to next interceptor
    next(function(response) {
        if (response.status == 401 && response.url !== '/login') {
            console.log('Token expired');
            store.dispatch('authExpired');
        }
    });
});

/**
 * Axios interceptor
 */
window.axios.interceptors.response.use(function(response) {
    return response;
}, function(error) {
    const originalRequest = error.config;

    if (error.response.status === 401 && !originalRequest._retry && originalRequest.url !== '/login') {
        console.log('Token expired');
        store.dispatch('authExpired');
    }

    return Promise.reject(error);
});