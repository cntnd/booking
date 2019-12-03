var gulp        = require('gulp');
var sass        = require('gulp-sass');
var minify      = require('gulp-minifier');
var zip         = require('gulp-zip');
var file        = require('gulp-file');
var del         = require('del');
var pkg         = require('./package.json');

gulp.task('watch', function () {
    gulp.watch('src/module/scss/**/*.scss', gulp.series('sass'));
});

// Compile sass into CSS
gulp.task('sass', function() {
    return gulp.src('src/module/scss/**/*.scss')
        .pipe(sass())
        .pipe(minify({
          minify: true,
          minifyCSS: true,
          getKeptComment: function (content, filePath) {
              var m = content.match(/\/\*![\s\S]*?\*\//img);
              return m && m.join('\n') + '\n' || '';
          }
        }))
        .pipe(gulp.dest("src/module/css/"));
});

gulp.task('zip', function() {
  return gulp.src(['src/module/**/*','!src/module/scss*'])
  		.pipe(zip('cntnd_booking-module.zip'))
  		.pipe(gulp.dest('dist'));
});

gulp.task('clean', function () {
  return del('dist/**/*');
});

// creates info.xml
gulp.task('info-xml', function () {
    var infoXml =
        '<?xml version="1.0" encoding="UTF-8"?>\n' +
        '<module>\n' +
        '<name>'+pkg.name+'</name>\n' +
        '<description>\n' +
        pkg.name+'\n' +
        pkg.description+'\n' +
        '\n' +
        'Autor '+pkg.author+'\n' +
        '\n' +
        'Version '+pkg.version+'\n' +
        '</description>\n' +
        '<type/>\n' +
        '<alias>'+pkg.name+'</alias>\n' +
        '</module>';

    return file('info.xml', infoXml, {src: true})
        .pipe(gulp.dest('src/module/'));
});

// creates plugin.xml
gulp.task('plugin-xml', function () {
    var pluginXml =
        '<general active="1">\n' +
                '<plugin_name>'+pkg.name+'</plugin_name>\n' +
                '<plugin_foldername>'+pkg.name+'</plugin_foldername>\n' +
                '<uuid>592369BC-2643-9A6F-5445-5CD465D60056</uuid>\n' +
                '<description>'+pkg.description+'</description>\n' +
                '<author>'+pkg.author+'</author>\n' +
                '<copyright>thomas@dasco.li</copyright>\n' +
                '<mail>thomas@dasco.li</mail>\n' +
                '<website>'+pkg.homepage+'</website>\n' +
                '<version>'+pkg.version+'</version>\n' +
        '</general>';

    return file('plugin.xml', pluginXml, {src: true})
        .pipe(gulp.dest('src/plugin/'));
});

gulp.task('xml', gulp.series('info-xml','plugin-xml'));

gulp.task('default', gulp.series('sass','watch'));

gulp.task('dist', gulp.series('clean','sass','xml','zip'));
