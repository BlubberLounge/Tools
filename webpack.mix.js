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
    .js('resources/js/auth.js', 'public/js')
    .js('resources/js/sw.js', 'public')
    .js('resources/js/s.js', 'public/js')
    .js('resources/js/home.js', 'public/js')

    // Dart
    .js('resources/js/dart/dartIndex.js', 'public/js')
    .js('resources/js/dart/dartInfo.js', 'public/js')
    // .js('resources/js/dart/dartPlayground.js', 'public/js') // needs to much memory when mixing

    // Dart Game
    .js('resources/js/dart/game/show.js', 'public/js')
    .js('resources/js/dart/game/dartSetup.js', 'public/js')
    .js('resources/js/dart/game/dartWaiting.js', 'public/js')
    .js('resources/js/dart/game/dartResult.js', 'public/js')
    .js('resources/js/dart/game/dartResultHeatmap.js', 'public/js')

    .js('resources/js/moving-average.js', 'public/js')
    .js('resources/js/IEC7064_page.js', 'public/js')

    // .js('resources/js/simApp.js', 'public/js')

    .js('resources/js/invitation.js', 'public/js')
    .js('resources/js/user-profilepicture.js', 'public/js')
    .js('resources/js/feedback-create.js', 'public/js')
    .js('resources/js/feedback-index.js', 'public/js')
    .js('resources/js/settings.js', 'public/js')
    .css('resources/css/custom.css', 'public/css')
    .sass('resources/sass/app.scss', 'public/css')
    .sass('resources/sass/auth.scss', 'public/css')
    .sass('resources/sass/sidebar.scss', 'public/css')
    .sass('resources/sass/dart.scss', 'public/css')
    .sass('resources/sass/dartboard.scss', 'public/css')
    .sass('resources/sass/dartboardResult.scss', 'public/css')
    .version()
    .sourceMaps();

// if (mix.inProduction()) {
//     mix.version();
// }
