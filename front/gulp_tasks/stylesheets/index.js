"use strict";
import fs from 'fs';
import gulp from 'gulp';
import plumber from 'gulp-plumber';
import sass from 'gulp-sass';
import postcss from 'gulp-postcss';
import autoprefixer  from 'autoprefixer';
import mqpacker from 'css-mqpacker';
import discardComments from 'postcss-discard-comments';
import nodeSass from 'node-sass';

import chalk from 'chalk';
import notify from 'gulp-notify';
import seq from 'run-sequence';

let SassTypes = nodeSass.types;

const conf = require('./config.json');

var browserSyncInstance;

var importer = function(url, file, done) {
  // look for modules installed through npm
  try {
    var newPath = require.resolve(url);
    fs.exists(newPath, function(exists) {
      if ( exists ) {
        return done({
          file: newPath
        });
      }
      return done({
        file: url
      });
    });

  } catch(e) {
    return done({
      file: url
    });
  }
}

function outputError(error) {
  console.log(chalk.red(error.message));
  notify.onError({
    title: 'Error in plugin <%= error.plugin %>',
    message: 'Error: <%= error.message %>',
    sound: true
  })(error);
  this.emit('end');
}
module.exports = function task(globalConf, browserSync) {
  browserSyncInstance = browserSync;

  let runParallelTasks = [];
  Object.keys(conf.sourceFiles).forEach(srcF => {
    let target = conf.sourceFiles[srcF],
        taskName = conf.taskName + ':build:' + target;
    gulp.task(taskName, () => {
        return buildSources(srcF, target);
    });
    runParallelTasks.push(taskName);
  });
  gulp.task(conf.taskName + ':build', cb => {
    seq(runParallelTasks, cb);
  });
  gulp.task(conf.taskName + ':watch', () => gulp.watch([...conf.sourceFiles, './**/*.scss'], [conf.taskName + ':build'] ));
};

function buildSources(pathToSCSS, targetDir) {


  return gulp.src(pathToSCSS)
    .pipe(plumber({
      errorHandler: outputError
    }))
    .pipe(sass({
      importer: importer,
      outputStyle: 'expanded',
      functions: {
        'asset-url($type)': function(_type) {
          var type = _type.getValue();
          var path = conf.css_paths[globalConf.PRODUCTION_BUILD ? 'prod' : 'dev'][type];
          return new SassTypes.String(path);
        }
      }
     }))
     .pipe(postcss([
        discardComments(),
        autoprefixer(conf.autoprefixer),
        mqpacker({
          sort: true
        })
     ]))
    .pipe(gulp.dest(targetDir))
    .pipe(browserSyncInstance.stream({match: '**/*.css'}));

}
