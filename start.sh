#!/usr/bin/env bash

# Prepare  
 
cd $(dirname $0)
currentPath=$(pwd -P);

cloud=$(php ./cluster/get_cloud.php);
echo "Start cloud: ${cloud}";

registerPath="/srv/www/conf/${cloud}/"

if [ ! -d ${registerPath} ]; then
    mkdir -p ${registerPath};
fi;

releaseInfoFileName="release.json"
releaseConfigFile="cluster/config_file"

releaseRegisterPlacementFile="release.path"
releaseRegisterPlacementLink="release.link"

prevReleasePlacement=""
previousConfig=""
previousConfigFile=""

internalExecStart="===>";
internalExecEnd="<===";

# Release registering
if [ -d ${registerPath} ]; then
    
    echo "-> Read previous release info ..."
    releasePlacementFile=${registerPath}${releaseRegisterPlacementFile}    
    if [ -f ${releasePlacementFile} ]; then
        prevReleasePlacement=$(cat ${releasePlacementFile});
        echo "--> Previous release placement $prevReleasePlacement";
        
        previousConfig=${prevReleasePlacement}"/"${releaseConfigFile};
        previousConfigFile=$(cat ${previousConfig});
         
        if [[ ${prevReleasePlacement} == ${currentPath} ]]; then
            echo "--> !!! Hard restart (same dir on current and new release)";
            echo "--> Stop processes from ${previousConfigFile}";
            echo ${internalExecStart};
            pm2 delete ${previousConfigFile};
            echo ${internalExecEnd};
        fi;
    fi;
fi;
    
echo "-> Generating config file ..."
echo ${internalExecStart};
/usr/bin/php cluster/configGeneration.php
echo ${internalExecEnd};
    
if [ -d ${registerPath} ]; then
    echo "-> Register current release ${currentPath} ..."

    registerPathRegistered=${registerPath}${releaseRegisterPlacementFile};
    
    echo "--> Placement file ${registerPathRegistered}";
    echo "${currentPath}" > "${registerPathRegistered}";
    
    registerLinkRegistered=${registerPath}${releaseRegisterPlacementLink};
     
    echo "--> Register link file ${registerLinkRegistered}";
    rm "${registerLinkRegistered}";
    ln -s "${currentPath}" "${registerLinkRegistered}";
    
    registerInfoFileCurrent=${currentPath}"/"${releaseInfoFileName};
    registerInfoFileRegistered=${registerPath}${releaseInfoFileName};
    
    if [ -f ${registerInfoFileCurrent} ]; then
        echo "--> Register info file to ${registerInfoFileRegistered}";
        rm ${registerInfoFileRegistered};
        ln -s ${registerInfoFileCurrent} ${registerInfoFileRegistered}
    fi
else 
  echo "!!! Path to register release not found ${registerPath}";
fi

configFile=$(cat ${currentPath}"/"${releaseConfigFile});

if [ -f ${configFile} ]; then
    echo "-> Starting up cluster from $configFile ..."
    echo ${internalExecStart};
    pm2 startOrRestart ${configFile}
    echo ${internalExecEnd};
    
    if [[ ${prevReleasePlacement} != "" ]] && [[ ${prevReleasePlacement} != ${currentPath} ]]; then
        echo "-> Stopping previous cluster ${previousConfigFile} ..."
        echo ${internalExecStart};
        pm2 delete ${previousConfigFile};
        echo ${internalExecEnd};
    fi
else
    echo "!!! Configuration file $configFile not found. Cancel starting."
    exit 1
fi

echo "+ Start complete.";
exit 0