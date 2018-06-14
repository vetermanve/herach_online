#!/usr/bin/env bash

bower install;

target=../public/vendor/;

while read -r line 
do
    echo "Execute $line ...";
    eval $line;
done < "install.txt"