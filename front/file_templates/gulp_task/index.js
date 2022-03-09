"use strict";

import gulp from 'gulp';

const conf = require('./config.json');

module.exports = function task(globalConf, browserSync) {
  gulp.task(conf.taskName, function() {
    gulp.src(conf.sourceFiles)
      .pipe(gulp.dest(globalConf.build.targetDir));
  });
}

