import { defineConfig } from "vite";
import typo3 from "vite-plugin-typo3";
import { resolve } from "node:path";

export default defineConfig({
    plugins: [typo3()],
    resolve: {
        alias: {
            "@": resolve(__dirname, 'packages/mens_circle/Resources/Private/Assets/'),
        }
    }
});