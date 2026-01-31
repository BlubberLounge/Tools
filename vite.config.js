import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                // Main files
                'resources/js/app.js',
                'resources/js/dark-mode.js',
                'resources/js/auth.js',
                'resources/js/home.js',
                'resources/js/events.js',

                // Dart
                'resources/js/dart/dartIndex.js',
                'resources/js/dart/dartInfo.js',
                'resources/js/dart/dartPlayground.js',

                // Dart Game
                'resources/js/dart/game/show.js',
                'resources/js/dart/game/dartSetup.js',
                'resources/js/dart/game/dartWaiting.js',
                'resources/js/dart/game/dartResult.js',
                'resources/js/dart/game/dartResultHeatmap.js',

                // Battery simulator
                'resources/js/simApp.js',

                // Other JS
                'resources/js/moving-average.js',
                'resources/js/IEC7064_page.js',
                'resources/js/invitation.js',
                'resources/js/user-profilepicture.js',
                'resources/js/feedback-create.js',
                'resources/js/feedback-index.js',
                'resources/js/settings.js',

                // Main CSS
                'resources/css/app.css',

                // Additional CSS
                'resources/sass/dart.scss',
                'resources/sass/dartboard.scss',
                'resources/sass/dartboardResult.scss',
            ],
            refresh: true,
        }),
    ],
});
