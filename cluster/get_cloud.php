<?php
chdir(__DIR__);

$releaseFile = '../release.json';

$releaseInfo = [];
if (file_exists($releaseFile)) {
    $releaseFileContent = file_get_contents($releaseFile);
    $releaseFileData =  \json_decode($releaseFileContent, true);
    if ($releaseFileData && isset($releaseFileData['id'])) {
        $releaseInfo  = $releaseFileData;
    }
}

echo $releaseInfo['slot'] ?? 'dev';
