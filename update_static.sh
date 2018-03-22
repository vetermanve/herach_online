#!/usr/bin/env bash

(cd static && bower install);

for line in $(cat static/in_use.txt); do
    echo "Copy $line ...";
    cp $line public/vendor/;
done;