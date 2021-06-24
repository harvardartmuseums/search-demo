const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    purge: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            boxShadow: {
                'inner-dark': 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.7)'
              },
          fontFamily: {
            'neutral': '"Neutral Regular 3"',
            'neutral-med':'"Neutral Medium 3"', 
            'neutral-bold': '"Neutral Bold 3"',
          },

        },
    },

    variants: {
        extend: {
            opacity: ['disabled'],
        },
    },

    plugins: [require('@tailwindcss/forms'), require('@tailwindcss/typography')],
};
