"use strict";

import gulp from 'gulp';

import del from 'del';

const conf = {
  "taskName": "clean",
  "sourceFiles": ["build/**/*", ".precompiled/**/*"],
}

module.exports = function task(globalConf) {
  return gulp.task(conf.taskName, function(cb) {
    del.sync(conf.sourceFiles);
    cb();
  });
}

