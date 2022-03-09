#!/bin/bash

echo 'build frontend...'
gulp --gulpfile frontend/gulpfile.babel.js build

echo 'sync assets...'
rsync -a --delete --exclude='atoms' --exclude='design_documentation' --exclude='dummy_content' --exclude='example_pages' --exclude='molecules' --exclude='styleguide' --exclude='templates' --exclude='index.html' frontend/build/ site/html/assets/

echo 'sync twig templates...'
rsync -a --delete --exclude='*.yml' --exclude='*.scss' --exclude='*.js' --exclude='*.jsx' frontend/source/pattern_lib/ site/craft/templates/_frontend

echo 'replace assets path...'
find site/html/assets/stylesheets/ -type f -name '*.css' -exec sed -i '' s:\"../../../assets/:\"/assets/assets/:g {} +

echo 'done!'
