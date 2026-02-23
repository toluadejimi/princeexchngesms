import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['DM Sans', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                mint: {
                    50: '#f0fdf9',
                    100: '#ccfbef',
                    200: '#99f6e0',
                    300: '#5eead4',
                    400: '#2dd4bf',
                    500: '#14b8a6',
                    600: '#0d9488',
                    700: '#0f766e',
                    800: '#115e59',
                    900: '#134e4a',
                },
                neon: {
                    green: '#39ff14',
                    blue: '#00d4ff',
                    mint: '#7fffd4',
                },
            },
            boxShadow: {
                'neon-green': '0 0 20px rgba(57, 255, 20, 0.5)',
                'neon-blue': '0 0 20px rgba(0, 212, 255, 0.5)',
                'neon-mint': '0 0 20px rgba(127, 255, 212, 0.4)',
                'glass': '0 8px 32px 0 rgba(31, 38, 135, 0.15)',
            },
            backgroundImage: {
                'gradient-mint-blue': 'linear-gradient(135deg, #14b8a6 0%, #0ea5e9 50%, #6366f1 100%)',
            },
        },
    },

    plugins: [forms],
};
