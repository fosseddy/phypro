#!/bin/bash

set -xe

rm -rf public/assets public/index.html

cd vue

npm install
npm run build

mv dist/* ../public

rm -rf node_modules dist

cd -
