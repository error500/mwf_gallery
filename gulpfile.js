/** 
Gulp commands :

	gulp imagemin : Takes images from img-resource and minify them in img
	gulp watch : Execute task compass

*/

var gulp = require ('gulp'),
	gulpFilter = require('gulp-filter'),
	imagemin = require('gulp-imagemin'),
	htmlmin  = require('gulp-htmlmin'),
	uglify  = require('gulp-uglify'),
	livereload = require('gulp-livereload'),
	compass = require('gulp-compass');


var jsFilter = gulpFilter('js/*.js');
var cssFilter = gulpFilter('css/*.css');
var scssFilter = gulpFilter('scss/*.scss');


gulp.task('imagemin', function () {
    gulp.src(['img-resource/*.(jpg|png|gif)'])
        .pipe(imagemin())
        .pipe(gulp.dest('img'));
});

gulp.task('minify', function() {
	gulp.src('*.html')
		.pipe(htmlmin({collapseWhitespace: true,minifyJS: true,minifyCSS: true}))
		.pipe(gulp.dest('min'));
	gulp.src('js-resource/*.js')
		.pipe(uglify())
		.pipe(gulp.dest('js'))
});




gulp.task ('default', function () {
	// Minifyed by compass : gulp not usefull
	/*return gulp.src('./css/app.css')
		.pipe(minifyCss())
		.pipe(gulp.dest('./css-min/'));*/
	
});


gulp.task('compass', function() {
  	gulp.src('./src/*.scss')
    	.pipe(compass({
    	config_file: './config.rb',
	 	css: 'css',
		sass: 'scss'
    }));
});


// A faire tourner apres un bower update
// A modifier pour ajouter des librairies
gulp.task ('package',['minify'],function () {
	gulp.src('libs/fancybox/source/**.*')
		.pipe(gulp.dest('./js/fancybox'));
	gulp.src('libs/masonry/dist/**min.js')
		.pipe(gulp.dest('./js/masonry'));
	gulp.src('libs/imagesloaded/**min.js')
		.pipe(gulp.dest('./js/imagesloaded'));		
	gulp.src('libs/imagesloaded/**min.js')
		.pipe(gulp.dest('./js/imagesloaded'));				
	gulp.src('libs/jquery/dist/**min.js')
		.pipe(gulp.dest('./js/jquery'));						
});



gulp.task('watch',function() {
	var server = livereload();
	gulp.watch('./scss/**.*',['compass']).on('change',function(event){
		console.log('Compilation de '+event.path);
    		livereload.reload();
		
	});
	gulp.watch('./img-resource/**.*',['imagemin']).on('change',function(event){
		console.log('Imagemin de '+event.path);
	});	
	//
	gulp.watch(['./img/*.*','./css/*.*']).on('change',function(event){
		console.log('Reload de '+event.path);
		livereload.changed();
	});
	gulp.watch(['**/*.php']).on('change',function(event){
		console.log('Reload de la page');
		livereload.reload();
	
	});

	gulp.watch(['js-resource/*.js']).on('change',function(event){
		console.log('Trazitement de ' + event.path);
		gulp.task('minify');
		livereload.reload();
	});
});