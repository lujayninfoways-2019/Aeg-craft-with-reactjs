"use strict";

import gulp from 'gulp';

const conf = require('./config.json');

const subTasks = Object.keys(conf.sourceFiles);

module.exports = function task(globalConf, browserSync) {

  subTasks.forEach(subTaskName => {

    gulp.task(`${conf.taskName}:${subTaskName}`, function() {
      return  gulp.src(conf.sourceFiles[subTaskName])
        .pipe(gulp.dest(`${globalConf.build.targetDir}/${subTaskName}`))
        .on("end", browserSync.reload);
    });

  });

  let subTasksToCall = subTasks.map(subTask => `${conf.taskName}:${subTask}`);

  gulp.task(conf.taskName + ':build', subTasksToCall);

  let filesToWatch = [...subTasks.map(subTask => {
    let files = conf.sourceFiles[subTask];
    if(Array.isArray(files))  return files.join(',');
    return files;
  })];

  gulp.task(`${conf.taskName}:watch`, () => {
    subTasks.forEach(subTask => {
      gulp.watch(conf.sourceFiles[subTask], [`${conf.taskName}:${subTask}`]);
    });
  });

}

