"use strict";

import gulp from 'gulp';
const conf = require('./config.json');

var util = require('util');

// Twig parser
// https://www.npmjs.com/package/gulp-twig
import twig from 'gulp-twig';

import rename from 'gulp-rename';


// Augment vinyl files with data
// https://www.npmjs.com/package/gulp-data
import readData from 'gulp-data';

// YAML parser
// https://www.npmjs.com/package/js-yaml
import yaml from 'js-yaml';

// Tap into gulp streams
// https://www.npmjs.com/package/gulp-tap
import tap from 'gulp-tap';

import path from 'path';
import fs from 'fs';
import invertObj from 'lodash.invert';

// Generate JSON from a dir structure
import readNavData from './_nav_data.js';

import responsiveImages from './_craft_responsive_images.js';

// Wrap contextless Twig templates with a page layout
import wrapWithLayout from './_wrap_with_layout.js';
import multiplyTwigs from './_multiply_twigs.js';

import plumber from 'gulp-plumber';
import chalk from 'chalk';
import notify from 'gulp-notify';

function outputError(error) {
  console.log(chalk.red(error.message));
  notify.onError({
    title: 'Error in plugin <%= error.plugin %>',
    message: 'Error: <%= error.message %>',
    sound: true
  })(error);
  this.emit('end');
}

function findDataFile(pathToTemplate, debug) {
  const magicalNamesReverse = invertObj(conf.magicalNames);
  let meaningfulPath = path.relative(`./${conf.precompileTarget}`, pathToTemplate);
  const sourceDirs = ['source/styleguide', 'source/pattern_lib'];
  let foundPath = false;

  sourceDirs.forEach(dir => {
    if(foundPath) return;

    let pathToTry = `${dir}/${meaningfulPath.replace('.twig', '.yml')}`;
    let pathsToTry = [pathToTry];

    if(~Object.keys(magicalNamesReverse).indexOf(path.basename(pathToTry, '.yml'))) {
      let otherName = pathToTry.replace(path.basename(pathToTry), magicalNamesReverse[path.basename(pathToTry, '.yml')] + '.yml');
      pathsToTry.push(otherName)
    }

    pathsToTry.forEach(pathToTry => {
      if(debug) console.log(pathToTry)
      if(foundPath) return;

      try {
        fs.statSync(path.resolve(pathToTry));
        foundPath = pathToTry;
      } catch(e) {
      }
    });
  })

  return foundPath;
}

function findMetaFile(pathToTemplate) {
  let meaningfulPath = path.relative(`./${conf.precompileTarget}`, pathToTemplate);
  const sourceDirs = ['source/styleguide', 'source/pattern_lib'];
  let foundPath = false;

  sourceDirs.forEach(dir => {
    let pathToTry = `${dir}/${path.dirname(meaningfulPath)}/_meta.yml`;
    if(foundPath) return;
    try {
      fs.statSync(path.resolve(pathToTry));
      foundPath = pathToTry;
    } catch(e) {
    }
  });

  return foundPath;
}

module.exports = function task(globalConf, browserSync) {

  const language = require('minimist')(process.argv.slice(2)).lang || globalConf.defaultLanguage;

  // Add a task to wrap layout information around
  // contextless Twig tamplates
  gulp.task(`${conf.taskName}:precompile`, [`${conf.taskName}:multitwig`], wrapWithLayout);
  gulp.task(`${conf.taskName}:multitwig`, multiplyTwigs);

  gulp.task(
    `${conf.taskName}:build`,
    [`${conf.taskName}:precompile`], // first precompile the differend yml files
    function() {

    // Read in conf.nav_folders contents as JSON
    const styleguideNavData = readNavData(
      conf.nav_data.folders,
      conf.nav_data.blacklist, // blacklist some files
      conf.nav_data.static_path // strip away parts of the path
                                // difference between browsersyncs server folder and source files
    );

    return gulp.src(`${conf.precompileTarget}/**/*.twig`)

      .pipe(plumber({
        errorHandler: outputError
      }))

      .pipe(readData(tmplFile => {

        let tmplFilePath = tmplFile.path;

        var data = {
          styleguideNavData
        };

        // Providing a YML file with data is optional
        // convention: same folder, named `data.yml`
        let dataFile = findDataFile(tmplFilePath);
        if(!dataFile) {
        }
        if(dataFile) {
          let tmplData = yaml.load(fs.readFileSync(dataFile));
          Object.assign(data, tmplData);
          responsiveImages(tmplData);
        }


        // Providing a YML file with metadata for styleguide pages
        // convention: same folder, named `_meta.yml`

        let metaFile = findMetaFile(tmplFilePath);
        if(metaFile) {
          let metaData = yaml.load(fs.readFileSync(metaFile));
          metaData = metaData[path.basename(tmplFilePath, '.twig')]; // we only need the data object named like the current yml file
          Object.assign(data, metaData)
        }

        // Provide language data
        try {
          let dir = fs.readdirSync(`${conf.baseFolder}/language/${language}/`);
          dir.forEach(file => {
            if(path.extname(file) === '.yml'){
              let fileData = yaml.load(fs.readFileSync(`${conf.baseFolder}/language/${language}/${file}`));
              Object.assign(data, fileData);
            }
          });
        } catch(e) { }

        // Add global data
        conf.twigData.forEach((dataItem) => {
          data[ dataItem[0] ] = dataItem[1]
        })

        return data;
      }))
      .pipe(twig({
        base: conf.baseFolder
      }))
      .pipe(rename((path) => {
        path.basename = path.basename.replace('.precompiled', '');
      }))
      .pipe(gulp.dest(globalConf.build.targetDir))
      .on("end", browserSync.reload);
  });

  gulp.task(`${conf.taskName}:watch`, () => gulp.watch([...conf.sourceFiles, ...conf.watchExtra], [ conf.taskName + ':build' ]))

}
