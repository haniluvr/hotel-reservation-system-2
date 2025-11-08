import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './node_modules/preline/dist/*.js',
    ],

    theme: {
        extend: {
            colors: {
                'primary-green': '#667f5f',
                'light-gray': '#f9fbf8',
            },
            fontFamily: {
                'cormorant': ['Cormorant Garamond', 'serif'],
                'montserrat': ['Montserrat', 'sans-serif'],
                'poppins': ['Poppins', 'sans-serif'],
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

  plugins: [
      forms,
      require('daisyui'),
  ],
    
    daisyui: {
        themes: [
            {
                belmont: {
                    "primary": "#667f5f",
                    "secondary": "#4a5d3a",
                    "accent": "#8b9c7a",
                    "neutral": "#3d4451",
                    "base-100": "#ffffff",
                    "base-200": "#f9fbf8",
                    "base-300": "#e6e9e2",
                    "info": "#3abff8",
                    "success": "#36d399",
                    "warning": "#fbbd23",
                    "error": "#f87272",
                },
            },
        ],
    },
};
