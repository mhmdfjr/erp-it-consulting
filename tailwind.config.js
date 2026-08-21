// tailwind.config.js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
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
                // Color Hunt Palette
                primary: {
                    DEFAULT: '#8100d1',
                    hover: '#6d00b0',
                    tint: '#f6edfd',
                },
                secondary: {
                    DEFAULT: '#b500b2',
                    tint: '#fdf2fd',
                },
                accent: {
                    pink: '#ff52a0',
                    'pink-tint': '#fff0f6',
                    peach: '#ffa47f',
                    'peach-tint': '#fff2ec',
                },
                // Neutrals
                'ink-black': '#15131b',
                'paper-white': '#ffffff',
                'mist-gray': '#f4f3f7',
                'fog-white': '#faf9fc',
                'slate-gray': '#6f6b7d',
                'ash-gray': '#9e9aa8',
                'border-gray': '#e7e5ed',

                // Semantic Status
                success: { DEFAULT: '#15803d', bg: '#ecfdf5' },
                warning: { DEFAULT: '#b45309', bg: '#fffbeb' },
                danger:  { DEFAULT: '#b91c1c', bg: '#fef2f2' },
                info:    { DEFAULT: '#0284c7', bg: '#f0f9ff' },
            },
            fontFamily: {
                sans: ['Inter', 'Sohne', ...defaultTheme.fontFamily.sans],
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
            borderRadius: {
                card: '12px',
                input: '8px',
                button: '8px',
                badge: '9999px',
                table: '8px',
            },
            boxShadow: {
                subtle: '0 1px 2px rgba(21,19,27,0.05)',
                elevated: '0 4px 14px rgba(129,0,209,0.08)',
                'focus-ring': '0 0 0 3px rgba(129,0,209,0.20)',
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
