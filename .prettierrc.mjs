/** @type {import("prettier").Config} */
export default {
  semi: false,
  singleQuote: true,
  plugins: ['prettier-plugin-astro', 'prettier-plugin-tailwindcss', 'prettier-plugin-organize-imports'],
  printWidth: 120,
}
