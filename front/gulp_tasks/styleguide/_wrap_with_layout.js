"use strict";

import gulp from 'gulp';
const conf = require('./config.json');

import tap from 'gulp-tap';
import filter from 'gulp-filter';

import path from 'path';

import rename from 'gulp-rename';


// Wrap each twig template in a static layout wrapper

export default function() {

  return gulp.src(`./${conf.precompileTarget}/**/*.twig`)
    .pipe(tap(file => {
      file.contents = Buffer.concat([
        // Above template
        new Buffer(conf.twigTemplateAbove),

        // Template contents
        file.contents,

        // Below template
        new Buffer(conf.twigTemplateBelow)
      ])
    }))
    // .pipe(rename((filepath) => {
    //   filepath.basename += '.precompiled';
    // }))
    .pipe(gulp.dest(conf.precompileTarget));

}
