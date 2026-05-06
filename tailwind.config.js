import defaultTheme from 'tailwindcss/defaultTheme'

/** @type {import('tailwindcss').Config} */
export default {
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
        sans:      ['Barlow', ...defaultTheme.fontFamily.sans],
        condensed: ['Barlow Condensed', ...defaultTheme.fontFamily.sans],
        serif:     ['Cormorant Garamond', ...defaultTheme.fontFamily.serif],
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
