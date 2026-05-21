const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

// Foolproof fix: Intercept webpack config and completely remove the ProgressPlugin
// and WebpackBar plugin from Webpack's build pipeline to prevent schema errors.
mix.webpackConfig(config => {
    if (config.plugins) {
        config.plugins = config.plugins.filter(plugin => {
            if (!plugin || !plugin.constructor) return true;
            const name = plugin.constructor.name;
            return name !== 'ProgressPlugin' && name !== 'WebpackBar';
        });
    }
});

mix.options({
    progress: false
});

mix.js('resources/js/app.js', 'public/js')
    .postCss('resources/css/app.css', 'public/css', [
        //
    ]);
