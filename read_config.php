<?php

$releaseData = file_exists('release.json') ? json_decode(file_get_contents('release.json'), 1) ?? [] : [];
$globalConfig = file_exists('config.json') ? json_decode(file_get_contents('config.json'), 1) ?? [] : [];

$globalConfig['release'] = $releaseData;

if (isset($releaseData['slot'], $globalConfig['db']['default']['database'])) {
    $globalConfig['db']['default']['database'] .= ':'.$releaseData['slot'];
}

return $globalConfig;
    
