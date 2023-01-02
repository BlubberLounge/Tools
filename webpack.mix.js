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
    .js('resources/js/app.js', 'public/js')
    // .js('resources/js/consoleText.js', 'public/js')
    // .js('resources/js/battery.js', 'public/js')
    // .js('resources/js/utils.js', 'public/js')
    // .js('resources/js/chartHelper.js', 'public/js')
    // .js('resources/js/simApp.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .css('resources/css/dark-mode.css', 'public/css')
    .css('resources/css/custom.css', 'public/css')
    .version()
    .sourceMaps();
