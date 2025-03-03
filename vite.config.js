import { defineConfig } from "vite";
import symfonyPlugin from "vite-plugin-symfony";
import vuePlugin from "@vitejs/plugin-vue";
import tailwindcss from '@tailwindcss/vite'


export default defineConfig({

    server: {
        host: '127.0.0.1',
        hmr: true,
        watch: {
            usePolling: false,
            include: ['**/*.php', '**/*.twig']
        }

    },
    plugins: [
        vuePlugin(),
        symfonyPlugin({
            stimulus: true,
            refresh: ['templates/**/*.twig']
        }),
        tailwindcss(),
        {
            name: 'twig-php-reload',
            handleHotUpdate ({ file, server }) {
                if (file.endsWith('.twig')) {
                    console.log('Reloading due to change in:', file);
                    server.ws.send({
                        type: 'full-reload',
                        path: '*'
                    });
                    return [];
                }
            }
        }
    ],
    build: {
        rollupOptions: {
            input: {
                app: "./assets/app.js",
                theme: "./assets/app.css",
                address: "./assets/js/address.js",
                leaflet: "./assets/js/leaflet.js"
            },
        }
    },
});
