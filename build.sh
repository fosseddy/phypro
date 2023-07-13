#!/bin/bash

set -xe

rm -rf public/assets views/index.php

mkdir -p views

cd vue

npm install
npm run build

mv dist/assets ../public
mv dist/index.html ../views/index.php

rm -rf node_modules dist

cd -
