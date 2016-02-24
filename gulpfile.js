var gulp       = require('gulp'),
    concat     = require('gulp-concat'),
    gulpif     = require('gulp-if'),
    sass       = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    uglify     = require('gulp-uglify'),
    gutil      = require('gulp-util'),
    exec       = require('child_process').exec;

// browsersync will only be used with the browsersync task
var browsersync = require('browser-sync').create();
var use_browsersync = false;

// setting this to false makes debugging easier and building the js a hundred times faster
var use_uglify = true;

var paths = {
    source_styles: ["web/css/*.scss"],
    source_js: ["web/js/**/*.js", "web/bower/**/*.js", "!web/js/build/*.js"],
    build_js: ["web/js/build/*.js"],
    php: ["protected/**/*.php"],
    std_js: [
        "web/bower/typeahead.js/dist/typeahead.bundle.min.js",
        "web/bower/bootstrap-sass/assets/javascripts/bootstrap.min.js",
        "web/js/jquery-ui-1.11.2.custom.min.js",
        "web/js/scrollintoview.js",
        "web/js/material/ripples.min.js",
        "web/js/material/material.min.js",
        "web/js/custom/*.js",
    ],
    leaflet_js: [
        "web/bower/leaflet/dist/leaflet.js",
        "web/bower/Leaflet.draw/dist/leaflet.draw.js",
        "web/bower/leaflet.locatecontrol/dist/L.Control.Locate.min.js",
        "web/js/Leaflet.Fullscreen/Control.FullScreen.js",
        "web/js/Leaflet.Control.Geocoder/Control.Geocoder.js",
        "web/js/leaflet.spiderfy.js",
        "web/js/leaflet.textmarkers.js",
    ],
}

gulp.task('default', ['std.js', 'leaflet.js', 'sass', 'ba_grenzen_geojson']);

gulp.task('watch', function () {
    use_uglify = false; // much better performance 
    gulp.watch(paths.source_js, ['std.js', 'leaflet.js']);
    gulp.watch(paths.source_styles, ['sass']);
});

gulp.task('browsersync', ['watch'], function() {
    use_browsersync = true;
    browsersync.init({
        proxy: "ratsinformant.local"
    });

    gulp.watch(paths.build_js).on("change", browsersync.reload);
    gulp.watch(paths.php     ).on("change", browsersync.reload);
});

// helper tasks

gulp.task('std.js', function () {
    return gulp.src(paths.std_js)
        .pipe(concat('std.js'))
        .pipe(gulpif(use_uglify, uglify()))
        .pipe(gulp.dest('web/js/build/'));
});

gulp.task('leaflet.js', function () {
    return gulp.src(paths.leaflet_js)
        .pipe(concat('leaflet.js'))
        .pipe(gulpif(use_uglify, uglify()))
        .pipe(gulp.dest('web/js/build/'));
});

gulp.task('sass', function () {
    return gulp.src(paths.source_styles)
        .pipe(sourcemaps.init())
        .pipe(sass({
            outputStyle: 'compressed'
        }).on('error', sass.logError))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('web/css/build/'))
        .pipe(gulpif(use_browsersync, browsersync.stream({match: "**/*.css"})));
});

gulp.task('ba_grenzen_geojson', function () {
    return exec('protected/yiic bagrenzengeojson web/js/ba_grenzen_geojson.js');
});
