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
                'hub-bg':           '#f8fafc',
                'hub-surface':      '#ffffff',
                'hub-surface-hover':'#f1f5f9',
                'hub-border':       '#d1d5db',
                'hub-text':         '#111827',
                'hub-text-sec':     '#6b7280',
                'hub-primary':      '#4b5563',
                'hub-primary-hover':'#374151',
                'hub-gold':         '#94a3b8',
                'hub-accent':       '#64748b',
            },
        },
    },

    plugins: [forms],
};
