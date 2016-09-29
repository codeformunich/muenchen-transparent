/*
Gulp is used to managed all sass/css and javascript resources.
 - Use `gulp watch` to rebuild css and js on every file change
 - Use `gulp browsersync` to automatically push changed resources to the browser
 - Append `--unuglified` to get uncompressed output
*/

var gulp       = require('gulp'),
    concat     = require('gulp-concat'),
    gulpif     = require('gulp-if'),
    sass       = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    uglify     = require('gulp-uglify'),
    expect     = require('gulp-expect-file'),
    yargs      = require('yargs'),
    process    = require('child_process');

// browsersync will only be used with the browsersync task
var browsersync = require('browser-sync').create();
var use_browsersync = false;

// Add an `--unuglified`options that makes building the js about ten times faster
var use_uglify = (yargs.argv["unuglified"] === undefined);

var paths = {
    source_sass: ["html/css/*.scss"],
    build_js: ["html/js/build/*.js"],
    php: ["protected/**/*.php"],
    std_js: [
        "html/bower/jquery/dist/jquery.min.js",
        "html/bower/typeahead.js/dist/typeahead.bundle.min.js",
        "html/bower/bootstrap-sass/assets/javascripts/bootstrap.min.js",
        "html/js/jquery-ui-1.11.2.custom.min.js",
        "html/js/scrollintoview.js",
        "html/js/material/ripples.min.js",
        "html/js/material/material.min.js",
        "html/js/custom/*.js",
    ],
    leaflet_js: [
        "html/js/build/ba-grenzen-geojson.js",
        "html/bower/leaflet/dist/leaflet-src.js",
        "html/bower/leaflet.draw/dist/leaflet.draw-src.js",
        "html/bower/leaflet.locatecontrol/dist/L.Control.Locate.min.js",
        "html/js/Leaflet.Control.Geocoder/Control.Geocoder.js",
        "html/js/leaflet.spiderfy.js",
        "html/js/leaflet.textmarkers.js",
        "html/js/antraegekarte.jquery.js",
    ],
    pdfjs_js: [
        "html/pdfjs/web/compatibility.js",
        "html/pdfjs/web/l10n.js",
        "html/pdfjs/build/pdf.js",
        "html/pdfjs/web/viewer.js",
    ],
    pdfjs_css: [
        "html/pdfjs/web/viewer.css",
    ]
};

gulp.task('default', ['std.js', 'leaflet.js', 'sass', 'pdfjs']);

gulp.task('watch', function () {
    gulp.watch(paths.std_js, ['std.js']);
    gulp.watch(paths.leaflet_js, ['leaflet.js']);
    gulp.watch(paths.source_sass, ['sass']);
    gulp.watch(paths.pdfjs_js, ['pdfjs.js']);
    gulp.watch(paths.pdfjs_css, ['pdfjs.css']);
});

gulp.task('browsersync', ['watch'], function() {
    use_uglify = false;
    use_browsersync = true;
    browsersync.init({
        proxy: "ratsinformant.local"
    });

    gulp.watch(paths.build_js).on("change", browsersync.reload);
    gulp.watch(paths.php     ).on("change", browsersync.reload);
});

// The real tasks

gulp.task('sass', function () {
    return gulp.src(paths.source_sass)
        .pipe(expect(paths.source_sass))
        .pipe(sourcemaps.init())
        .pipe(sass({
            outputStyle: 'compressed'
        }).on('error', sass.logError))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('html/css/build/'))
        .pipe(gulpif(use_browsersync, browsersync.stream({match: "**/*.css"})));
});


gulp.task('std.js', function () {
    return gulp.src(paths.std_js)
        .pipe(expect(paths.std_js))
        .pipe(concat('std.js'))
        .pipe(gulpif(use_uglify, uglify()))
        .pipe(gulp.dest('html/js/build/'));
});

gulp.task('leaflet.js', ['ba-grenzen-geojson'], function () {
    return gulp.src(paths.leaflet_js)
        .pipe(expect(paths.leaflet_js))
        .pipe(concat('leaflet.js'))
        .pipe(gulpif(use_uglify, uglify()))
        .pipe(gulp.dest('html/js/build/'));
});

gulp.task('ba-grenzen-geojson', function () {
    return process.exec('$(git rev-parse --show-toplevel)/protected/yiic bagrenzengeojson html/js/build/ba-grenzen-geojson.js');
});

gulp.task('pdfjs', ['pdfjs.js', 'pdfjs.css']);

gulp.task('pdfjs.js', function () {
    return gulp.src(paths.pdfjs_js)
        .pipe(expect(paths.pdfjs_js))
        .pipe(concat('build.js'))
        .pipe(gulpif(use_uglify, uglify()))
        .pipe(gulp.dest('html/pdfjs/web/'));
});

gulp.task('pdfjs.css', function () {
    return gulp.src(paths.pdfjs_css)
        .pipe(expect(paths.pdfjs_css))
        .pipe(concat('build.css'))
        .pipe(sourcemaps.init())
        .pipe(sass({
            outputStyle: 'compressed'
        }).on('error', sass.logError))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('html/pdfjs/web/'))
        .pipe(gulpif(use_browsersync, browsersync.stream({match: "**/*.css"})));
});
