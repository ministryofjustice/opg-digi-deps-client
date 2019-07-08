var Encore = require('@symfony/webpack-encore');

Encore
    // directory where compiled assets will be stored
    .setOutputPath('web/assets/')
    // public path used by the web server to access the output path
    .setPublicPath('/assets')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('application', './src/AppBundle/Resources/assets/javascripts/main.js')

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableSourceMaps(true)
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(false)

    // uncomment if you use Sass/SCSS files
    .enableSassLoader(function(sassOptions) {
        sassOptions.includePaths = [
            'node_modules/govuk_frontend_toolkit/stylesheets',
            'node_modules/govuk-elements-sass/public/sass'
        ];
    })

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()


    .copyFiles({
        from: './node_modules/govuk-frontend/assets',
    })

    // .copyFiles({ from: './node_modules/govuk_frontend_toolkit/images' })
    // .copyFiles({ from: './node_modules/govuk_template_mustache/assets/images' })
    // .copyFiles({ from: './node_modules/govuk_template_mustache/assets/stylesheets/images' })

    .copyFiles({
        from: './src/AppBundle/Resources/assets/images',
        to: '../images/[path][name].[ext]',
    })
;

module.exports = Encore.getWebpackConfig();
