var gulp       = require('gulp'),
    concat     = require('gulp-concat'),
    gulpif     = require('gulp-if'),
    sass       = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    uglify     = require('gulp-uglifyjs'),
    gutil      = require('gulp-util');

// browsersync will only be used with the browsersync task
var browsersync = require('browser-sync').create();
var use_browsersync = false;

var paths = {
    scss: ['html/css/*.scss'],
    source_js: ['html/js/**/*.js', "!html/js/build/*.js"],
    build_js: ["html/js/build/*.js"],
    php: ["protected/**/*.php"],
    std_js: [
        "html/js/jquery-ui-1.11.2.custom.min.js",
        "html/js/scrollintoview.js",
        "html/js/antraegekarte.jquery.js",
        "html/js/bootstrap.min.js",
        "html/js/material/ripples.min.js",
        "html/js/material/material.min.js",
        "html/js/typeahead.js/typeahead.bundle.min.js",
        "html/js/index.js"
    ],
    leaflet_js: [
        "html/js/leaflet/dist/leaflet.js",
        "html/js/Leaflet.Fullscreen/Control.FullScreen.js",
        "html/js/Leaflet.Control.Geocoder/Control.Geocoder.js", 
        "html/js/Leaflet.draw-0.2.3/dist/leaflet.draw.js",
        "html/js/leaflet.spiderfy.js", 
        "html/js/leaflet.textmarkers.js", 
        "html/js/leaflet.locatecontrol/dist/L.Control.Locate.min.js"
    ],
}

gulp.task('default', ['std.js', 'leaflet.js', 'sass']);

gulp.task('watch', function () {
    gulp.watch(paths.source_js, ['std.js', 'leaflet.js']);
    gulp.watch(paths.scss, ['sass']);
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
    gulp.src(paths.std_js)
        .pipe(concat('std.js'))
        .pipe(uglify())
        .pipe(gulp.dest('html/js/build/'))
        .on('end', function(){ gutil.log('main js finished'); });
});

gulp.task('leaflet.js', function () {
    gulp.src(paths.leaflet_js)
        .pipe(concat('leaflet.js'))
        .pipe(uglify())
        .pipe(gulp.dest('html/js/build/'))
        .on('end', function(){ gutil.log('leaflet finished'); });
});

gulp.task('sass', function () {
    gulp.src(paths.scss)
        .pipe(sourcemaps.init())
        .pipe(sass({
            outputStyle: 'compressed'
        }).on('error', sass.logError))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('html/css'))
        .on('end', function(){ gutil.log('css finished'); })
        .pipe(gulpif(use_browsersync, browsersync.stream({match: "**/*.css"})));
});
