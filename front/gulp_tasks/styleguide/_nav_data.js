"use strict";

var util = require('util');
var fs = require('fs'),
    path = require('path');

var util = require('util');

import yaml from 'js-yaml';

import { reducePathLeft } from './_helpers.js';

/**
 * Sort dir data structure by property "order" in _meta.yml
 * Runs recursively
 * @param  {Object} structure Data to operate on in this step
 * @return {undefined}
 */
function sortDirs(structure) {

  if(structure.children) {
    structure.children.sort((a, b) => {
      return a.navMetadata.order - b.navMetadata.order;
    })

    structure.children.forEach(child => sortDirs(child));
  }

}

/**
 * Recurse a given directory and build a data object
 * Basic structure taken from here: http://stackoverflow.com/a/11194896
 * @param  {String} filename     starting point
 * @param  {Array}  blacklist    files to ignore
 * @param  {Number} reducePathBy strip leftmost portion of paths in output
 * @param  {Object} metaData     metadata aquired in parent directory
 * @return {Object}              data object describing dir structure with meta data
 */

function fixFilename(filename) {
  var filename = filename.replace('.yml', '.html');
  return filename.replace('data.html', 'index.html');
}
function dirTree(filename, blacklist = [], reducePathBy = 1, metaData) {

  var stats = fs.lstatSync(filename);
  var fixedFilename = fixFilename(filename);

  var info = {
    path: reducePathLeft(fixedFilename, reducePathBy),
    name: path.basename(fixedFilename, '.html'),
    menuTitle: path.basename(fixedFilename, '.html'),
    full_path: fixedFilename,
    navMetadata: {}
  };


  if(stats.isDirectory()) {
    info.type = "folder";

    info.children = fs.readdirSync(filename).map(function(child) {

      // Whitelist only .yml files and dirs (no extension)
      if(!~['', '.yml'].indexOf(path.extname(child))) return;

      if(path.basename(child) == '_meta.yml') {
        // We have found a file containing metadata; read it
        metaData = yaml.load(fs.readFileSync(`${filename}/${child}`));

        if(metaData) {
          info.navMetadata = metaData;

          if(metaData.menuTitle) {
            info.menuTitle = metaData.menuTitle; // Overwrite file name with human-friendly name
          }
        }
      }

      // Blacklist specific files - if needed
      if(!!~blacklist.indexOf(path.basename(child))) return;

      // If _meta.yml holds a top level key of `child` and a setting ignore: true, step over
      if(metaData && metaData[child] && metaData[child].ignore == true) return;

      // Repeat for contents, pass down metadata
      return dirTree(filename + '/' + child, blacklist, reducePathBy, metaData);

    }).filter(val => val); // remove undefined values (as a result of blacklisting)

  } else {

    if(metaData && metaData[info.name]) {
    // We got meta data for current filename
      Object.assign(info, metaData[info.name]);
    }

    info.type = "file";
  }

  return info;
}

if (module.parent == undefined) {
  console.log(util.inspect(dirTree(process.argv[2]), false, null));
}



export default function readStyleguideNavData(dirs, blacklist, reducePathBy) {

  // Build a virtual directory as starting point
  var navData = { children: [] };

  // Populate with configured directories
  dirs.forEach(dir => {
    navData.children.push(dirTree(dir, blacklist, reducePathBy));
  });

  sortDirs(navData);  // Sort dirs bei "order" property in _meta.yml
  return navData
}
