#!/bin/bash

echo 'sync assets...'
rsync -a --delete --exclude='atoms' --exclude='design_documentation' --exclude='dummy_content' --exclude='example_pages' --exclude='molecules' --exclude='styleguide' --exclude='templates' --exclude='index.html' frontend/build/ html/assets/

echo 'sync twig templates...'
rsync -a --delete --exclude='*.yml' --exclude='*.scss' --exclude='*.js' --exclude='*.jsx' frontend/source/pattern_lib/ craft/templates/_frontend

echo 'replace assets path...'
sed s:\"../../../assets/:\"/assets/assets/:g /var/www/html/assets/stylesheets/style.css > style-sed.css && mv style-sed.css style.css
sed s:\"../../../assets/:\"/assets/assets/:g /var/www/html/assets/stylesheets/print.css > print-sed.css && mv print-sed.css print.css

sed s:\"../../../assets/:\"/assets/assets/:g /var/www/html/site/html/assets/stylesheets/style.css > style-sed.css && mv style-sed.css style.css
sed s:\"../../../assets/:\"/assets/assets/:g /var/www/html/site/html/assets/stylesheets/print.css > print-sed.css && mv print-sed.css print.css


echo 'done!'
