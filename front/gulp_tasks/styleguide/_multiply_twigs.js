"use strict";

import gulp from 'gulp';
const conf = require('./config.json');

import path from 'path';
import filter from 'gulp-filter';
import tap from 'gulp-tap';

import fs from 'fs';
import fsex from 'fs-extra';
import glob from 'glob';

import { reducePathLeft } from './_helpers.js';

export default function multiplyTwigFilesForYMLs() {
  return gulp.src(conf.sourceFiles)
    .pipe(filter(['**', '!**/_meta.yml']))
    .pipe(tap(file => {

      if(path.extname(file.path) == '.twig') {
        return; // We only want YML files
      }

      // All significant data files are worked on now,
      // fill some important variables;
      // it will all lead to copying

      let dir = path.dirname(file.path);
      let relDir = reducePathLeft(path.relative('./source', dir) + '/', conf.static_path - 1);
      let filename = path.basename(file.path, '.yml');
      let template = glob.sync(`${dir}/*.twig`);
      let targetFilename = conf.magicalNames[filename] || filename;
      let targetPath = path.resolve(`./${conf.precompileTarget}/${relDir}/${targetFilename}.twig`)


      if(!template.length) return;

      template = template[0];

      fsex.copySync(template, targetPath)

    }));

}
