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
    .js('resources/js/sw.js', 'public/js')
    .js('resources/js/auth.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .css('resources/css/custom.css', 'public/css')
    .version()
    .sourceMaps();
