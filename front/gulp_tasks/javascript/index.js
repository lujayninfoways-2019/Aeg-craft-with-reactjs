"use strict";

import gulp from 'gulp';

const conf = require('./config.json');

let tap = require('gulp-tap');

import initJsBundler from './_bundler.js';

module.exports = function task(globalConf, browserSyncInstance) {

  [[':build', false], [':watch', true]].forEach(def => {
    gulp.task(conf.taskName + def[0], function() {
      return gulp.src(conf.sourceFiles)
        .pipe(tap(file => {
          initJsBundler({entryFile: file.path, watch: def[1]}, conf, globalConf, browserSyncInstance);
      }));
    });
  });

}
