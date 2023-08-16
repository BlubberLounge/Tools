const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
    .js('resources/js/app.js', 'public/js')                 // Main js file
    .js('resources/js/dark-mode.js', 'public/js')           // Manages theme toggling
    .js('resources/js/simApp.js', 'public/js')
    // .js('resources/js/sw.js', 'public/js')
    .js('resources/js/auth.js', 'public/js')
    .js('resources/js/dart.js', 'public/js')
    .js('resources/js/dartSetup.js', 'public/js')
    .js('resources/js/dartResult.js', 'public/js')
    .js('resources/js/user-profilepicture.js', 'public/js')
    .js('resources/js/feedback-create.js', 'public/js')
    .js('resources/js/feedback-index.js', 'public/js')
    .css('resources/css/custom.css', 'public/css')
    .sass('resources/sass/app.scss', 'public/css')
    .sass('resources/sass/auth.scss', 'public/css')
    .sass('resources/sass/sidebar.scss', 'public/css')
    .sass('resources/sass/dart.scss', 'public/css')
    .sass('resources/sass/dartboard.scss', 'public/css')
    .version()
    .sourceMaps();

// if (mix.inProduction()) {
//     mix.version();
// }
