import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Modules/**/resources/views/**/*.blade.php',
        './app/Modules/**/Livewire/**/*.php',
    ],

    theme: {
        extend: {
            colors: {
                'ink-black': '#17191c',
                'paper-white': '#ffffff',
                'mist-gray': '#f2f2f3',
                'fog-white': '#fafafb',
                'slate-gray': '#777b86',
                'ash-gray': '#979799',
                'border-gray': '#e5e5e7',
                'blush-peach': '#fbe1d1',
                'sienna-brown': '#5d2a1a',
                success: { DEFAULT: '#1a7f4e', bg: '#e6f4ec' },
                warning: { DEFAULT: '#b5750a', bg: '#fdf1de' },
                danger: { DEFAULT: '#c0362c', bg: '#faeae8' },
                info: { DEFAULT: '#2563a8', bg: '#e9f1fa' },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                caption: ['12px', { lineHeight: '1.4' }],
                label: ['13px', { lineHeight: '1.4' }],
                'body-sm': ['14px', { lineHeight: '1.5' }],
                body: ['15px', { lineHeight: '1.5' }],
                'body-lg': ['16px', { lineHeight: '1.5' }],
                'heading-sm': ['18px', { lineHeight: '1.3' }],
                heading: ['22px', { lineHeight: '1.3' }],
                'heading-lg': ['28px', { lineHeight: '1.25' }],
            },
            spacing: {
                18: '4.5rem',
            },
            borderRadius: {
                card: '12px',
                input: '8px',
                button: '8px',
                badge: '9999px',
                table: '8px',
            },
            boxShadow: {
                subtle: '0 1px 2px rgba(23,25,28,0.06)',
                elevated: '0 4px 12px rgba(23,25,28,0.10)',
                'focus-ring': '0 0 0 3px rgba(37,99,168,0.25)',
            },
            width: {
                sidebar: '240px',
                'sidebar-collapsed': '64px',
            },
            height: {
                topbar: '56px',
            },
        },
    },

    plugins: [forms],
};
