import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/js/chat/chatbox.js"],
            refresh: true
        })
    ],
    resolve: {
        alias: {
            "@": "/resources/js"
        }
    }
});
