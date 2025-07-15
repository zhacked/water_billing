const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .postCss('resources/css/app.css', 'public/css', [
      require('@tailwindcss/postcss'),  // ✅ CORRECT
      require('autoprefixer'),
   ])
   .version()
   .disableNotifications();

mix.browserSync({
   proxy: '127.0.0.1:8000',
   files: [
      'app/**/*.php',
      'resources/views/**/*.php',
      'resources/js/**/*.js',
      'resources/css/**/*.css',
      '!public/css/app.css',
   ],
   open: true,
   notify: false,
});

mix.webpackConfig({
   watchOptions: {
      ignored: /node_modules/,
      poll: 1000,
   },
});
