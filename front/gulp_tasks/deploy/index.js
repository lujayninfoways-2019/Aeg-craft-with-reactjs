"use strict";

import gulp from 'gulp';

const conf = require('./config.json');

const argv = require('minimist')(process.argv.slice(2));
import rsync from 'gulp-rsync';
import exit from 'gulp-exit';

const rsyncDefaultConf = {
  root: conf.projectRoot,
  recursive: true,
  clean: true
};

const rsyncConf = Object.assign({}, rsyncDefaultConf, conf.targets[argv.target]);

module.exports = function task(globalConf, browserSync) {
  gulp.task(conf.taskName, function() {

    if(!argv.target) {
      throw new Error(`Please specify deploy target with --target
        Known targets: ${Object.keys(conf.targets).join(', ')}\n`);
    }

    if(!conf.targets[argv.target]) {
      throw new Error(`Target ${argv.target} is not configured!`);
    }

    return gulp.src(conf.sourceFiles)
      .pipe(rsync(rsyncConf))
      .pipe(exit());
  });
}

