'use strict';

const gulp = require('gulp'),
    gutil = require('gulp-util'),
    sass = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    del = require('del'),
    uglifycss = require('gulp-uglifycss'),
    uglify = require('gulp-uglify'),
    concat = require('gulp-concat'),
    scsslint = require('gulp-sass-lint'),
    jshint = require('gulp-jshint'),
    replace = require('gulp-replace'),
    rename = require('gulp-rename'),
    now = new Date().getTime(),
    postcss = require('gulp-postcss');

var config = {
    sass: {
        includePaths: [
            'node_modules/govuk_frontend_toolkit/stylesheets',
            'node_modules/govuk-elements-sass/public/sass'
        ]
    },
    jsSrc: 'src/AppBundle/Resources/assets/javascripts',
    imgSrc: 'src/AppBundle/Resources/assets/images',
    sassSrc: 'src/AppBundle/Resources/assets/scss',
    viewsSrc: 'src/AppBundle/Resources/views',
    webAssets: 'web/assets/' + now,
};

const cleanAssets = () => { // Clear web assets folder and formatted report css folder
    return del([
        'web/assets/*',
        config.viewsSrc + '/css/*'

    ]);
}

const lintSass = () => { // sass quality control
    return gulp.src([
        config.sassSrc + '/**/*.scss',
        config.sassSrc + '/*.scss'])
        .pipe(scsslint({
            options: {
                configFile: '.sass-lint.yml'
            }
        }))
        .pipe(scsslint.format());
}

const lintJS = () => { // JS quality control
    return gulp.src([config.jsSrc + '/**/*.js'])
        .pipe(jshint())
        .pipe(jshint.reporter('default'));
}

const CompileFormattedReportSassToCSS = () => {
    return gulp.src(config.sassSrc + '/formatted-report.scss')
        .pipe(sourcemaps.init())
        .pipe(sass(config.sass).on('error', sass.logError))
        .pipe(uglifycss())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(config.viewsSrc + '/css'));
}

const copyFormattedReportCSSToTwigVersion = () => {
    return gulp.src(config.viewsSrc + '/css/formatted-report.css')
    .pipe(rename(config.viewsSrc + '/css/formatted-report.css.twig'))
    .pipe(gulp.dest('./'));
}

const deleteFormattedReportCSSVersion = () => {
    return del([config.viewsSrc + '/css/formatted-report.css']);
}

const buildApplicationCSSFromSass = () => { // Compile sass files, uglify, copy
    return gulp.src([
        config.sassSrc + '/application.scss',
        config.sassSrc + '/application-print.scss'])
        .pipe(sourcemaps.init())
        .pipe(sass(config.sass).on('error', sass.logError))
        .pipe(uglifycss())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));
};

const makeImagePathsAbsoluteInGovUKCSSThenCopy = () => {
    return gulp.src([
            './node_modules/govuk_template_mustache/assets/stylesheets/fonts.css',
            './node_modules/govuk_template_mustache/assets/stylesheets/govuk-template.css',
        ])
        .pipe(replace('images/', '/images/'))
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));
}

const copyGovUKAssets = () => {
    return gulp.src('node_modules/govuk-frontend/assets/**/*')
        .pipe(gulp.dest(config.webAssets + '/stylesheets'));
}
const copyGovUKFonts = () => {
    return gulp.src('node_modules/govuk_template_mustache/assets/stylesheets/fonts/*')
        .pipe(gulp.dest(config.webAssets + '/stylesheets/fonts'));
}
const copyAllImages = () => {
    return gulp.src([
            './node_modules/govuk_frontend_toolkit/images/**/*',
            './node_modules/govuk_template_mustache/assets/images/*',
            './node_modules/govuk_template_mustache/assets/stylesheets/images/**/*',
            './node_modules/govuk_template_mustache/assets/stylesheets/images/gov.uk_logotype_crown.png',
            config.imgSrc + '/**/*'
        ])
        .pipe(gulp.dest('./web/images'));
}

const concatJSThenMinifyAndCopy = () => { // Only minify if prod
    return gulp.src([
            './node_modules/govuk-frontend/all.js',
            './node_modules/govuk_template_mustache/assets/javascripts/govuk-template.js',
            './node_modules/govuk_frontend_toolkit/javascripts/govuk/show-hide-content.js',
            config.jsSrc + '/govuk/polyfill/*.js',
            config.jsSrc + '/modules/*.js',
            config.jsSrc + '/main.js'])
        .pipe(sourcemaps.init())
        .pipe(concat('application.js'))
        .pipe(uglify())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest(config.webAssets + '/javascripts'));
}

const copyJQuery = () => {
    return gulp.src([
        './node_modules/jquery/dist/jquery.min.js'])
        .pipe(gulp.dest(config.webAssets + '/javascripts'));
}

const checkCSSAccessibility = () => {
    return gulp.src(config.webAssets + '/stylesheets/*.css')
      .pipe(
        postcss([
            require('postcss-wcag-contrast')({compliance: 'AA'})
        ])
      );
}

// Compile formatted report CSS and copy to twig, then delete the .css version
gulp.task('rebuild-formatted-report-css', gulp.series(CompileFormattedReportSassToCSS, copyFormattedReportCSSToTwigVersion, deleteFormattedReportCSSVersion));

gulp.task('sass', gulp.series(lintSass, buildApplicationCSSFromSass));

gulp.task('app-js', gulp.series(lintJS, concatJSThenMinifyAndCopy));

gulp.task('lint', gulp.series(lintSass, lintJS, checkCSSAccessibility));

// Prepare and build all assets.
gulp.task('default', gulp.series(
    cleanAssets,
    gulp.parallel(
        'sass',
        makeImagePathsAbsoluteInGovUKCSSThenCopy,
        copyAllImages,
        copyGovUKAssets,
        copyGovUKFonts,
        'app-js',
        copyJQuery,
        'rebuild-formatted-report-css'
    ), checkCSSAccessibility));

// Watch sass, images and js and recompile as Development
gulp.task('watch', gulp.series(function () {
    gulp.watch([
        config.sassSrc + '/**/*',
        config.sassSrc + '/*',
        config.imgSrc + '/**/*',
        config.jsSrc + '/**/*.js',
        config.jsSrc + '/*.js'],
        { interval: 1000, usePolling: true },
        gulp.series('default'));
}));
