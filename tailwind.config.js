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
                display: ['Fraunces', 'Figtree', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                // Rosa suave (marca principal) — desaturado respecto al original
                brand: {
                    50:  '#fdf2f5',
                    100: '#fbe5ec',
                    200: '#f6ccd9',
                    300: '#eea7bd',
                    400: '#e07d9b',
                    500: '#cf5f80',
                    600: '#b84968',
                    700: '#993a54',
                    800: '#7d3245',
                    900: '#6a2d3c',
                },
                // Verde salvia (acento secundario) — apagado, nada chillón
                sage: {
                    50:  '#f3f6f2',
                    100: '#e5ebe2',
                    200: '#cbd7c5',
                    300: '#a7bb9e',
                    400: '#7f9a73',
                    500: '#5f7d53',
                    600: '#4b6541',
                    700: '#3d5135',
                    800: '#33422e',
                    900: '#2c3828',
                },
                // Crema / papel — base cálida en lugar de gris frío
                cream: {
                    50:  '#fdfcf9',
                    100: '#f8f5ee',
                    200: '#f1ebdf',
                    300: '#e7dcc8',
                },
                ink: {
                    700: '#4a4540',
                    800: '#36322e',
                    900: '#27241f',
                },
            },
            borderRadius: {
                blob: '42% 58% 56% 44% / 48% 42% 58% 52%',
            },
            boxShadow: {
                soft: '0 12px 40px -12px rgba(106, 45, 60, 0.18)',
                lift: '0 18px 50px -16px rgba(106, 45, 60, 0.28)',
            },
            keyframes: {
                'fade-up': {
                    '0%':   { opacity: '0', transform: 'translateY(10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                'pop-in': {
                    '0%':   { opacity: '0', transform: 'scale(.94)' },
                    '100%': { opacity: '1', transform: 'scale(1)' },
                },
                'float-slow': {
                    '0%,100%': { transform: 'translateY(0) rotate(0deg)' },
                    '50%':     { transform: 'translateY(-10px) rotate(3deg)' },
                },
            },
            animation: {
                'fade-up': 'fade-up .5s ease-out both',
                'pop-in': 'pop-in .35s ease-out both',
                'float-slow': 'float-slow 7s ease-in-out infinite',
            },
        },
    },

    plugins: [forms],
};
