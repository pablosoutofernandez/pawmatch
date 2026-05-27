import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50:  '#fff0f4',
                    100: '#ffe0e9',
                    200: '#ffc0d3',
                    300: '#ff8fab',
                    400: '#ff5f85',
                    500: '#f02d5e',
                    600: '#e0184a',
                    700: '#bc1040',
                    800: '#9b1039',
                    900: '#831237',
                },
            },
        },
    },

    plugins: [forms],
};
