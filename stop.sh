#!/usr/bin/env bash

echo "-> Stopping release ..."

configFile=`cat cluster/config_file`;
if [ -f $configFile ]; 
    then
        echo "-> Stopping release $configFile ..."
        pm2 delete "$configFile"
    else
        echo "Configuration file $configFile not found. Cancel Stopping."
        exit 1
    fi
    
echo "+ Stopping complete."