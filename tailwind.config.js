/** @type {import('tailwindcss').Config} */
import Translator from 'bazinga-translator';
module.exports = {
  content: [ 
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    screens: {
      'xs': '375px',
      'sm': '576px',
      'md': '768px',
      'lg': '992px',
      'xl': '1200px',
      
      // ... other breakpoints
    },
    extend: {
      backgroundImage: {
        'custom-gradient': 'linear-gradient(to bottom right, #109646, #106496)',
      },
      colors:{
        'custom-gradient': 'linear-gradient(to bottom right, #109646, #106496)',
        'main-green': '#109646',
        'secondary-green': '#106496',
        'bg-white': '#ffffff',
        'white': '#ffffff',

      }
    },
  },
  plugins: [],
}

// 'main-green': '#109646'
// 'main-blue': '#106496'