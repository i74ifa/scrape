import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";
import tailwindcss from "@tailwindcss/vite";
import path from "path";

export default defineConfig({
    plugins: [
        laravel({
            input: ["src/app.css", "src/app.jsx"],
            refresh: true,
            buildDirectory: "build/admin",
            publicDirectory: "../../public",
            // hotFile: "public/hot-admin",
        }),
        react(),
        tailwindcss(),
    ],

    build: {
        rollupOptions: {
            external: ["qs-esm"],
        },
    },
    resolve: {
        // Force a single React instance. Transitive deps like
        // @react-stately/data (pulled in by react-aria-components / HeroUI for
        // useAsyncList) can otherwise bind to a second React copy, which makes
        // their hooks read a null dispatcher ("Cannot read properties of null
        // (reading 'useReducer')").
        dedupe: ["react", "react-dom"],
        alias: {
            "@": "/src",
            'vendor/tightenco/ziggy': path.resolve('vendor/tightenco/ziggy/dist'),
        },
    },
});
