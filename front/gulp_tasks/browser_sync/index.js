"use strict";

import gulp from 'gulp';
const conf = require('./config.json');


module.exports = function tasks(globalConf, browserSync) {

  return gulp.task(conf.taskName + ':start', function() {
    browserSync.init(conf.browserSyncConf);
  });

  gulp.task(conf.taskName + ':reload', function() {
    browserSync.reload();
  });


}

