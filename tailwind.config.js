import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import colors from 'tailwindcss/colors'; // 👈 Add this import

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './resources/**/*.ts',
        './resources/**/*.jsx',
        './resources/**/*.tsx',
        './storage/framework/views/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // 👇 Extend the colors to restore full palette
            colors: {
                red: colors.red,
                orange: colors.orange,
                yellow: colors.yellow,
                green: colors.green,
                blue: colors.blue,
                gray: colors.gray,
                slate: colors.slate,
            },
        },
    },

    plugins: [forms],
};
