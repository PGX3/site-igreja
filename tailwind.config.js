import defaultTheme from 'tailwindcss/defaultTheme'

/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.vue',
    './resources/js/**/*.js',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans:      ['Inter', ...defaultTheme.fontFamily.sans],
        condensed: ['Arial', ...defaultTheme.fontFamily.sans],
        serif:     ['sans-serif', ...defaultTheme.fontFamily.serif],
      },
      colors: {
        gold: '#c9a84c',
      },
      transitionDuration: {
        400: '400ms',
      },
    },
  },
  plugins: [],
}
