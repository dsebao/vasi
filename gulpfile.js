// Gulp Nodes
let gulp = require('gulp');
let plumber = require('gulp-plumber');
let watch = require('gulp-watch');
let minifyCSS = require('gulp-minify-css');
let uglify = require('gulp-uglify');
let rename = require('gulp-rename');
let notify = require('gulp-notify');
let include = require('gulp-include');
let sass = require('gulp-sass');
let autoprefixer = require('gulp-autoprefixer');
let concat = require('gulp-concat');
let imagemin = require('gulp-imagemin');
let sourcemaps = require('gulp-sourcemaps');
let newer = require('gulp-newer');

// Configuration file to keep your code DRY
var data = require('./gulpconfig.json');
var paths = data.paths;

sass.compiler = require('node-sass');

// Error Handling
let onError = function (err) {
    console.log('An error occurred:', err.message);
    this.emit('end');
};


// Copy Assets
gulp.task('assets', function (done) {
    // Copy all JS files
    gulp
        .src(paths.node + '/jquery/dist/jquery.min.js')
        .pipe(gulp.dest(paths.js + '/core/jquery'));

    gulp
        .src(paths.node + '/bootstrap/dist/js/**/*.js')
        .pipe(gulp.dest(paths.js + '/core/bootstrap4'));

    // Copy all Bootstrap SCSS files
    gulp
        .src(paths.node + '/bootstrap/scss/**/*.scss')
        .pipe(gulp.dest(paths.scss + '/bs4/'));

    done();
});

//Styles
gulp.task('scss', () => {
    return gulp.src(paths.scss + '/style.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(autoprefixer('last 4 version'))
        //.pipe(minifyCSS({ keepSpecialComments: 1, processImport: false }))
        .pipe(sourcemaps.write('./maps'))
        .pipe(gulp.dest('./'))
        .pipe(notify({ message: 'Scss task complete' }));
});

gulp.task('js', ()  =>{
	return gulp.src([
        paths.jquery,
        paths.bs4,
        paths.js + '/libs/**/*.js',
        paths.js + '/development/**/*.js'
    ])

    .pipe(concat(paths.js + '/scripts.js'))
    .pipe(gulp.dest('./'))
    .pipe(rename({suffix: '.min'}))
    .pipe(uglify())
    .pipe(gulp.dest('./'))
    .pipe(notify({ message: 'Scripts task complete' }));
});


// Watch task -- this runs on every save.
gulp.task( 'watch', ()  =>{
	// Watch all .scss files
	gulp.watch( paths.scss + '/**/**/*.*css', gulp.series('scss') );
	// Watch main style.scss file for new inclusions
	gulp.watch(  paths.scss + '/style.scss', gulp.series('scss') );

	// Watch js files
	gulp.watch( paths.js + '/development/**/*.js', gulp.series('js') );

});


gulp.task( 'start', gulp.parallel('assets', 'scss', 'js'));

gulp.task( 'default', gulp.parallel('watch'));
