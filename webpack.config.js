var Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
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
    .addEntry('app', './assets/js/app.js')
    .addEntry('home', './assets/js/home.js')
    .addEntry('profile', './assets/js/profile.js')
    .addEntry('project', './assets/js/project.js')
    .addEntry('calendar', './assets/js/calendar.js')
    .addEntry('category', './assets/js/category.js')
    .addEntry('espace_asso', './assets/js/espace_asso.js')
    .addEntry('espace_asso_home', './assets/js/espace_asso_home.js')
    .addEntry('espace_asso_article', './assets/js/espace_asso_article.js')
    .addEntry('espace_asso_article_list', './assets/js/espace_asso_article_list.js')
    .addEntry('espace_asso_event', './assets/js/espace_asso_event.js')
    .addEntry('espace_asso_event_list', './assets/js/espace_asso_event_list.js')
    .addEntry('espace_asso_roombook', './assets/js/espace_asso_roombook.js')
    .addEntry('espace_asso_infos', './assets/js/espace_asso_infos.js')
    .addEntry('espace_asso_pages', './assets/js/espace_asso_pages.js')
    .addEntry('espace_admin_ressources', './assets/js/espace_admin_ressources.js')
    .addEntry('espace_admin_news_edit', './assets/js/espace_admin_news_edit.js')
    .addEntry('espace_admin_news_list', './assets/js/espace_admin_news_list.js')
    .addEntry('espace_admin_roombooks', './assets/js/espace_admin_roombooks.js')
    //.addEntry('page1', './assets/page1.js')
    //.addEntry('page2', './assets/page2.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning()
    //.enableVersioning(Encore.isProduction())

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    .copyFiles({
        from: './assets/images',

        // optional target path, relative to the output dir
        to: 'images/[path][name].[ext]',

        // if versioning is enabled, add the file hash too
        //to: 'images/[path][name].[hash:8].[ext]',

        // only copy files matching this pattern
        //pattern: /\.(png|jpg|jpeg)$/
    })
    .copyFiles({
        from: './assets/js-static',
        to: 'js/[path][name].[ext]'
    })
    .copyFiles({
        from: './assets/fonts',
        to: 'fonts/[path][name].[ext]'
    })
    .copyFiles({
        from: './node_modules/moment/min',
        to: 'js/moment/[path][name].[ext]'
    })

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()

    // uncomment if you use API Platform Admin (composer req api-admin)
    //.enableReactPreset()
    //.addEntry('admin', './assets/admin.js')
;

module.exports = Encore.getWebpackConfig();
