
/**
 * This is a page specific seperate vue instance initializer
 */

// include vue common libraries, plugins and components
require('../../../eventmie-pro/resources/js/vue_common');

//CUSTOM
require('../bootstrap.js');

//CUSTOM

/**
 * Below are the page specific plugins and components
  */

// for using time
window.moment   = require('moment-timezone');  

// add Vue-router with SEO friendly configurations
import VueRouter from 'vue-router';
Vue.use(VueRouter);

// import component for vue routes
import MyGuests from './components/guests/MyGuests';

import MyGlists from './components/glists/MyGlists';

// glist_id
window.glist_id   = glist_id;  

// add Veevalidate for auto validation
window.VeeValidate = require('vee-validate');
Vue.use(VeeValidate)

// vue routes
const routes = new VueRouter({
    mode: 'history',
    base: '/',
    linkExactActiveClass: 'there',
    routes: [
        {
            path: path ? '/'+path+'/myglists/guest-'+glist_id : '/myglists/guest-'+glist_id,
            // Inject  props based on route.query values for pagination
            props: (route) => ({
                page: route.query.page,
                // category: route.query.category,
                // search: route.query.search,
                // search: route.query.price,
                // start_date: route.query.start_date,
                // end_date: route.query.end_date,
            }),
            name: 'myguests',
            component: MyGuests,

        },

        {
            path: path ? '/'+path+'/myglists' : '/myglists',
            // Inject  props based on route.query values for pagination
            props: (route) => ({
                page: route.query.page,
                // category: route.query.category,
                // search: route.query.search,
                // search: route.query.price,
                // start_date: route.query.start_date,
                // end_date: route.query.end_date,
            }),
            name: 'myglists',
            component: MyGlists,

        },
    ],
});


/**
 * This is where we finally create a page specific
 * vue instance with required configs
 * element=app will remain common for all vue instances
 *
 * make sure to use window.app to make new Vue instance
 * so that we can access vue instance from anywhere
 * e.g interceptors 
 */
window.app = new Vue({
    el: '#eventmie_app',
    router: routes,
});