/*
Gulp is used to managed all sass/css and javascript resources.
 - Use `gulp watch` to rebuild css and js on every file change
 - Append `--unuglified` to get uncompressed output
*/

const gulp = require('gulp'),
    terser = require('gulp-terser'),
    concat = require('gulp-concat'),
    gulpif = require('gulp-if'),
    postcss = require('gulp-postcss'),
    autoprefixer = require('autoprefixer'),
    sass = require('gulp-sass'),
    sourcemaps = require('gulp-sourcemaps'),
    uglify = require('gulp-uglify'),
    expect = require('gulp-expect-file'),
    yargs = require('yargs');

// Add an `--unuglified`options that makes building the js about ten times faster
const use_uglify = (yargs.argv["unuglified"] === undefined);

const paths = {
    source_sass: ["html/css/*.scss"],
    build_js: ["html/js/build/*.js"],
    php: ["protected/**/*.php"],
    std_js: [
        "node_modules/jquery/dist/jquery.min.js",
        "node_modules/typeahead.js/dist/typeahead.bundle.min.js",
        "node_modules/bootstrap-sass/assets/javascripts/bootstrap.min.js",
        "html/js/jquery-ui-1.11.2.custom.min.js",
        "html/js/scrollintoview.js",
        "html/js/material/ripples.min.js",
        "html/js/material/material.min.js",
        "html/js/custom/*.js",
    ],
    leaflet_js: [
        "html/js/build/ba-grenzen-geojson.js",
        "node_modules/leaflet/dist/leaflet-src.js",
        "node_modules/leaflet-draw/dist/leaflet.draw-src.js",
        "node_modules/leaflet.locatecontrol/dist/L.Control.Locate.min.js",
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
    ],
    bower_emulate: [
        "mediaelement",
        "fullcalendar",
        "leaflet-draw",
        "ckeditor",
        "isotope",
        "list.js",
        "mediaelement",
        "selectize",
        "shariff",
        "moment",
        "fullcalendar"
    ]
};

function taskBuildJsMain() {
    return gulp.src(paths.std_js)
        .pipe(sourcemaps.init())
        .pipe(concat('std.js'))
        .pipe(terser())
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('html/js/build/'));
}

function taskBuildCss() {
    return gulp.src(paths.source_sass)
        .pipe(sourcemaps.init())
        .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
        .pipe(postcss([autoprefixer()]))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('html/css/build/'));
}

async function taskCopyFiles() {
    await gulp.src("node_modules/fullcalendar/main.min.js").pipe(gulp.dest('./html/fullcalendar/'));
    await gulp.src("node_modules/fullcalendar/main.min.css").pipe(gulp.dest('./html/fullcalendar/'));
    await gulp.src("node_modules/fullcalendar/locales/de.js").pipe(gulp.dest('./html/fullcalendar/locales/'));
}

gulp.task('default', gulp.parallel(taskBuildJsMain, taskCopyFiles, taskBuildCss));

/*
gulp.task('default', ['std.js', 'leaflet.js', 'sass', 'pdfjs']);

gulp.task('watch', function () {
    gulp.watch(paths.std_js, ['std.js']);
    gulp.watch(paths.leaflet_js, ['leaflet.js']);
    gulp.watch(paths.source_sass, ['sass']);
    gulp.watch(paths.pdfjs_js, ['pdfjs.js']);
    gulp.watch(paths.pdfjs_css, ['pdfjs.css']);
});

// The real tasks


gulp.task('leaflet.js', ['emulate-bower', 'ba-grenzen-geojson'], function () {
    return gulp.src(paths.leaflet_js)
        .pipe(expect(paths.leaflet_js))
        .pipe(concat('leaflet.js'))
        .pipe(gulpif(use_uglify, uglify()))
        .pipe(gulp.dest('html/js/build/'));
});

gulp.task('pdfjs', ['pdfjs.js', 'pdfjs.css']);

gulp.task('pdfjs.js', ['emulate-bower'], function () {
    return gulp.src(paths.pdfjs_js)
        .pipe(expect(paths.pdfjs_js))
        .pipe(concat('build.js'))
        .pipe(gulpif(use_uglify, uglify()))
        .pipe(gulp.dest('html/pdfjs/web/'));
});

gulp.task('pdfjs.css', ['emulate-bower'], function () {
    return gulp.src(paths.pdfjs_css)
        .pipe(expect(paths.pdfjs_css))
        .pipe(concat('build.css'))
        .pipe(sourcemaps.init())
        .pipe(sass({
            outputStyle: 'compressed'
        }).on('error', sass.logError))
        .pipe(sourcemaps.write('./'))
        .pipe(gulp.dest('html/pdfjs/web/'));
});

// Copy the old bower dependencies from /node_modules/[package-name] to /html/bower/[package-name]
gulp.task('emulate-bower', function () {
    let folders = paths.bower_emulate.map((folder) => "node_modules/" + folder + "/**" + "/*");
    return gulp.src(folders, {base: "node_modules/"}).pipe(gulp.dest('html/bower/'));
});
*/
