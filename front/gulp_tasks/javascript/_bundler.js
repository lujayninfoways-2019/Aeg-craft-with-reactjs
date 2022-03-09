"use strict";

import gulp from 'gulp';

let buffer = require('vinyl-buffer'),
    source = require('vinyl-source-stream'),
    browserify = require('browserify'),
    watchify = require('watchify'),
    babel = require('babelify'),
    envify = require('envify/custom'),
    uglifyify = require('uglifyify'),
    browserifyShim = require('browserify-shim'),
    util = require('gulp-util'),
    notify = require('gulp-notify'),
    sourcemaps = require('gulp-sourcemaps'),
    gulpif = require('gulp-if'),
    path = require('path'),
    rename = require('gulp-rename');

export default function initJsBundler({entryFile, watch, transpileJSX, fileExtensions, renameExtname}, conf, globalConfig, browserSync) {

  var files = [];

  var babelPlugins = [
  ];


  if(transpileJSX) babelPlugins.push('transform-react-jsx');

  var bundler = browserify({
    entries: entryFile,
    extensions: fileExtensions || ['.js'],
    debug: !globalConfig.PRODUCTION_BUILD,
    cache: {},
    packageCache: {},
    noParse: conf.noParse.map(packageName => require.resolve(packageName))
  })
    .transform(browserifyShim)
    .transform(babel, {
      presets: ['es2015'],
      plugins: babelPlugins
    })
    .transform(envify());

  if(globalConfig.PRODUCTION_BUILD) {
    bundler = bundler.transform({
      global: true
    }, 'uglifyify');
  }

  if(watch) {
    bundler = watchify(bundler);
  }

  function rebundleJS() {
    var targetPath = path.dirname(path.relative(`${globalConfig.ROOT}/source`, entryFile));
    var targetPath = targetPath.replace('pattern_lib/', '');
    bundler.bundle()
      .on('error', notify.onError({
        message: 'Error: <%= error.message %>',
        sound: true
       }))
      .pipe(source(path.basename(entryFile)))
      .pipe(rename((path) => {
        path.extname = renameExtname || ".js";
      }))
      .pipe(buffer())
      .pipe(gulpif(!globalConfig.PRODUCTION_BUILD, sourcemaps.init({ loadMaps: true })))
      .pipe(gulpif(!globalConfig.PRODUCTION_BUILD, sourcemaps.write('./')))
      .pipe(gulp.dest(`${conf.targetDir}/${targetPath}`))
      .pipe(browserSync.stream());
  }

  bundler.on('update', rebundleJS);
  bundler.on('log', util.log);

  return rebundleJS();
}
