import { defineConfig } from 'vitest/config'
import react from '@vitejs/plugin-react'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
    plugins: [
        react(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
    ],
    test: {
        environment: 'jsdom',
        globals: true,
        setupFiles: './vitest.setup.js',
    },
})