<?php

$releaseData = file_exists('release.json') ? json_decode(file_get_contents('release.json'), 1) ?? [] : [];
$globalConfig = file_exists('config.json') ? json_decode(file_get_contents('config.json'), 1) ?? [] : [];

$globalConfig['release'] = $releaseData;

if (isset($releaseData['slot'])) {
    $slotName = $releaseData['slot'];
    
    if (isset($globalConfig['db']['default']['database'])) {
        $globalConfig['db']['default']['database'] .= ':'.$slotName;    
    }
    
    if (isset($globalConfig['error']['no_debug_slots'])) {
        $globalConfig['error']['debug'] = !in_array($slotName, $globalConfig['error']['no_debug_slots']);
    }
}

return $globalConfig;
    
