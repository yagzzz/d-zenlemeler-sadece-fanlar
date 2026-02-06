/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: [
                    'Open Sans',
                    'ui-sans-serif',
                    'system-ui',
                    'sans-serif',
                ],
            },
        },
    },
    plugins: [],
};
