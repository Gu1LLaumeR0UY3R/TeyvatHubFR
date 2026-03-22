import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'hub-bg':           '#0a0a0f',
                'hub-surface':      '#12121a',
                'hub-surface-hover':'#1c1c28',
                'hub-border':       '#2a2a3a',
                'hub-text':         '#e8e8f0',
                'hub-text-sec':     '#8888a8',
                'hub-primary':      '#c4a84a',
                'hub-primary-hover':'#d4b85a',
                'hub-gold':         '#f0b232',
                'hub-accent':       '#7b9de0',
            },
        },
    },

    plugins: [forms],
};
