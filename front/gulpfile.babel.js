'use strict';

const globalConfig = require('./config.json');

import gulp from 'gulp';
import requireTasks from 'require-dir-all';
import seq from 'run-sequence';

import BrowserSync from 'browser-sync';
var browserSync = BrowserSync.create();


globalConfig.PRODUCTION_BUILD = process.env.NODE_ENV == 'production';
globalConfig.ROOT = __dirname;

requireTasks('gulp_tasks', {
  recursive:     true,
  indexAsParent: true,
  includeFiles:  /^[^_].*\.(js)$/,
  map: function(reqTask) {
    reqTask.exports(globalConfig, browserSync); // Register task and pass global configuration to it
  }
});

gulp.task('_build', cb => {
  seq('js:build', ['copy_assets:build', 'stylesheets:build'], 'styleguide:build', cb);
});

gulp.task('_watch', cb => {
  seq(['copy_assets:watch', 'styleguide:watch', 'stylesheets:watch', 'js:watch'], cb);
});

gulp.task('build', ['clean', '_build'], () => process.exit());

gulp.task('deploy', cb => {
  seq('clean', '_build', 'rsync', cb);
});

gulp.task('default', cb => {
  seq('clean', '_build', '_watch', 'browser_sync:start', cb);
});

