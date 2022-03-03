let mix = require('laravel-mix');
let fs = require('fs');
let path = require('path');
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application, as well as bundling up your JS files.
 |
 */
mix.setPublicPath(path.normalize('./public'));

const webpack = require('webpack');

const isCiBuild = !!process.env.CI;

const VERSION = process.env.CI_COMMIT_TAG || process.env.CI_COMMIT_SHA || 'custom';

let plugins = [
    new webpack.DefinePlugin({
        VERSION: JSON.stringify(VERSION), // Pass version to a bundle
    }),
    new webpack.ContextReplacementPlugin(/moment[/\\]locale$/, /ru|en|pt/),
];

// If we have ANALYZER environment variable -> generate webpack analyzer
if (process.env.ANALYZER) {
    const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

    plugins.push(new BundleAnalyzerPlugin({
        analyzerPort: 8887,
    }));
}

// If we use CI pipeline -> send source maps to sentry
if (isCiBuild) {
    const SentryWebpackPlugin = require('@sentry/webpack-plugin');

    plugins.push(
        new SentryWebpackPlugin({
            include: 'public/',
            ignoreFile: '.sentrycliignore',
            configFile: '.sentryclirc',
            release: VERSION,
        }),
    );
}

mix
    .combine([
        'node_modules/vue-toastr/dist/vue-toastr.css',
        'node_modules/bootstrap-tour/build/css/bootstrap-tour.css',
        'node_modules/pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css',
        'resources/assets/css/default.css',
    ], 'public/css/style.css')
    .js('resources/assets/js/application.js', 'public/js')
    .sass('resources/assets/sass/app.scss', 'public/css/application.css')
    .copy('resources/assets/css/loader.css', 'public/css/loader.css')
    .sourceMaps()
    .webpackConfig({
        // module: {
        //     rules: [
        //         {
        //             test: /\.pug$/,
        //             oneOf: [
        //                 {
        //                     resourceQuery: /^\?vue/,
        //                     use: ['pug-plain-loader']
        //                 },
        //                 {
        //                     use: ['raw-loader', 'pug-plain-loader']
        //                 }
        //             ]
        //         }
        //     ]
        // },
        resolve: {
            extensions: ['.js', '.vue', '.json'],
            alias: {
                '@': path.join(__dirname, '/resources/assets/js'),
                // '~': __dirname,
            },
        },
        plugins: plugins,
        module: {
            rules: [
                {
                    test: /\.pug$/,
                    loader: 'pug-plain-loader',
                },
                // {
                //     test: /\.css$/,
                //     loaders: ['css']
                // },
                // {
                //     test: /\.css$/,
                //     use: ['style-loader', 'css-loader'],
                //     include: [helpers.root('src', 'assets')]
                // }
            ],
        },
    });

if (process.env.NODE_ENV === 'production') {
    mix.webpackConfig({
        output: {
            chunkFilename: 'js/[name].[contenthash].js',
        },
    }).version();
}


// Full API
// mix.js(src, output);
// mix.react(src, output); <-- Identical to mix.js(), but registers React Babel compilation.
// mix.extract(vendorLibs);
// mix.sass(src, output);
// mix.standaloneSass('src', output); <-- Faster, but isolated from Webpack.
// mix.less(src, output);
// mix.stylus(src, output);
// mix.browserSync('my-site.dev');
// mix.combine(files, destination);
// mix.babel(files, destination); <-- Identical to mix.combine(), but also includes Babel compilation.
// mix.copy(from, to);
// mix.copyDirectory(fromDir, toDir);
// mix.minify(file);
// mix.sourceMaps(); // Enable sourcemaps
// mix.version(); // Enable versioning.
// mix.disableNotifications();
// mix.setPublicPath('path/to/public');
// mix.setResourceRoot('prefix/for/resource/locators');
// mix.autoload({}); <-- Will be passed to Webpack's ProvidePlugin.
// mix.webpackConfig({}); <-- Override webpack.config.js, without editing the file directly.
// mix.then(function () {}) <-- Will be triggered each time Webpack finishes building.
// mix.options({
//   extractVueStyles: false, // Extract .vue component styling to file, rather than inline.
//   processCssUrls: true, // Process/optimize relative stylesheet url()'s. Set to false, if you don't want them touched.
//   purifyCss: false, // Remove unused CSS selectors.
//   uglify: {}, // Uglify-specific options. https://webpack.github.io/docs/list-of-plugins.html#uglifyjsplugin
//   postCss: [] // Post-CSS options: https://github.com/postcss/postcss/blob/master/docs/plugins.md
// });
