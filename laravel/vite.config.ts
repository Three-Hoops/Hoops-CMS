import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { visualizer } from 'rollup-plugin-visualizer'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
        visualizer({
            filename: 'public/build/bundle-stats.html',
            open: false,
            gzipSize: true,
            brotliSize: true,
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    test: {
        environment: 'jsdom',
        globals: true,
        include: ['resources/js/**/*.test.{ts,js}'],
        coverage: {
            provider: 'v8',
            reporter: ['clover', 'text'],
            include: ['resources/js/**/*.{ts,vue}'],
            exclude: [
                'resources/js/components/ui/**',
                'resources/js/app.ts',
                'resources/js/types/**',
            ],
        },
    },
})
