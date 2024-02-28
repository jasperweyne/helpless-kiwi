const defaultTheme = require('tailwindcss/defaultTheme')

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
      fontFamily: {
        'sans': ['"Raleway"', ...defaultTheme.fontFamily.sans],
      },
    }
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
