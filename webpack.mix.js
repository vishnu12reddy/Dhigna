const mix = require('laravel-mix');

require('laravel-mix-polyfill');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

// mix.js('resources/js/app.js', 'public/js')
mix.js('resources/js/vendor/eventmie-pro/events_show/index.js', 'public/js/events_show_v1.9.js')
    // .js('resources/js/vendor/eventmie-pro/bookings_organiser/index.js', 'public/js/bookings_organiser_v1.8.js')
    // .js('resources/js/vendor/eventmie-pro/bookings_customer/index.js', 'public/js/bookings_customer_v1.8.js')
    .js('resources/js/vendor/eventmie-pro/events_manage/index.js', 'public/js/events_manage_v1.8.js')
    // .js('resources/js/vendor/eventmie-pro/myevents/index.js', 'public/js/myevents_v1.8.js')
    // .js('resources/js/organiser/index.js', 'public/js/organiser.js')
    // .js('resources/js/vendor/eventmie-pro/events_listing/index.js', 'public/js/events_listing_v1.8.js')
    // .js('resources/js/vendor/eventmie-pro/welcome/index.js', 'public/js/welcome_v1.8.js')
    // .js('resources/js/pos_bookings/index.js', 'public/js/pos_bookings.js')
    // .js('resources/js/scanner_bookings/index.js', 'public/js/scanner_bookings.js')
    // .js('resources/js/myguests/index.js', 'public/js/myguests.js')
    // .js('resources/js/sub_organizers/index.js', 'public/js/sub_organizers.js')
    // .js('resources/js/vendor/eventmie-pro/ticket_scan/index.js', 'public/js/ticket_scan_v1.8.js')
    // .js('resources/js/manage_reviews/index.js', 'public/js/manage_reviews.js')

    // use vue 2
    .vue({ version: 2 })
    .webpackConfig({
        optimization: {
            providedExports: false,
            sideEffects: false,
            usedExports: false
        },
        //CUSTOM
        resolve: {
            fallback: {
                "crypto": false,
                "crypto-browserify": require.resolve('crypto-browserify'), //if you want to use this module also don't forget npm i crypto-browserify 
            } 
        }
        //CUSTOM
    })
    .override((config) => {
        delete config.watchOptions;
    });
