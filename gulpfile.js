var gulp = require('gulp'),
    sass = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    uglify = require('gulp-uglifyjs'),
    concat = require('gulp-concat'),
    gutil = require('gulp-util');

gulp.task('std.js', function () {
    gulp.src(["./html/js/jquery-ui-1.11.2.custom.min.js", "./html/js/scrollintoview.js", "./html/js/antraegekarte.jquery.js",
            "./html/js/bootstrap.min.js", "./html/js/material/ripples.min.js", "./html/js/material/material.min.js",
            "./html/js/typeahead.js/typeahead.bundle.min.js", "./html/js/index.js"])

        .pipe(concat('std.js'))
        .pipe(uglify())
        .pipe(gulp.dest('./html/js/build/'))
        .on('end', function(){ gutil.log('main js finished'); });
});

gulp.task('leaflet.js', function () {
    gulp.src(["./html/js/leaflet/dist/leaflet.js", "./html/js/Leaflet.Fullscreen/Control.FullScreen.js",
            "./html/js/Leaflet.Control.Geocoder/Control.Geocoder.js", "./html/js/Leaflet.draw-0.2.3/dist/leaflet.draw.js",
            "./html/js/leaflet.spiderfy.js", "./html/js/leaflet.textmarkers.js", "./html/js/leaflet.locatecontrol/dist/L.Control.Locate.min.js"])

        .pipe(concat('leaflet.js'))
        .pipe(uglify())
        .pipe(gulp.dest('./html/js/build/'))
        .on('end', function(){ gutil.log('leaflet finished'); });
});

gulp.task('sass', function () {
    gulp.src('./html/css/*.scss')
        .pipe(sourcemaps.init())
        .pipe(sass({
            outputStyle: 'compressed'
        }).on('error', sass.logError))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('./html/css'))
        .on('end', function(){ gutil.log('css finished'); });
});

gulp.task('default', ['std.js', 'leaflet.js', 'sass']);

gulp.task('watch', function () {
    gulp.watch('./html/js/**/*.js', ['std.js', 'leaflet.js']);
    gulp.watch('./html/css/*.scss', ['sass']);
});
