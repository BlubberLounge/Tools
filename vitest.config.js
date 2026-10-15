import { defineConfig } from 'vitest/config';

// JavaScript unit tests (tests/js). Separate from vite.config.js so the
// Laravel plugin is not loaded for tests.
export default defineConfig({
    test: {
        include: ['tests/js/**/*.test.js'],
        environment: 'node',
    },
});
