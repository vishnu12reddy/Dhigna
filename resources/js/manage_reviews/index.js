
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


// Vue.component('manage-reviews', require('./components/ManageReviews').default);

import ManageReviews from './components/ManageReviews';

// vue routes
const routes = new VueRouter({
  mode: 'history',
  base: '/',
  linkExactActiveClass: 'there',
  routes: [
      {
          path: '/manage_reviews',
          // Inject  props based on route.query values for pagination
          props: (route) => ({
              page: route.query.page,
              // category: route.query.category,
              // search: route.query.search,
              // search: route.query.price,
              // start_date: route.query.start_date,
              // end_date: route.query.end_date,
          }),
          name: 'manage-reviews',
          component: ManageReviews,

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