<?php

chdir(__DIR__);

function g_log($msg)
{
    echo $msg . "\n";
}

$releaseInfo = [
    'id' => 'local',
];

$releaseFile = '../release.json';
if (file_exists($releaseFile)) {
    $releaseFileContent = file_get_contents($releaseFile);
    $releaseFileData =  \json_decode($releaseFileContent, true);
    if ($releaseFileData && isset($releaseFileData['id'])) {
        $releaseInfo  = $releaseFileData;
    }
}

g_log('Release config compile: '.$releaseInfo['id']);

$result = [];

$performance = 1;
$version     = $releaseInfo['id'];

$cloud = [
    'all'     => 4,
//    'user'    => 10,
//    'api'     => 4,
//    'testing' => 2,
];

$area = $releaseInfo['slot'] ?? 'dc';

$argsTemplate = [
    'dc'    => $area, //gethostname(),
//    'cloud' => 'cloud_one',
//    'host'  => 'localhost',
];

$appConfigTemplate = [
    "name"         => $area,
    "kill_timeout" => 4000,
    "script"       => "daemon.php",
    'args'         => '',
    "instances"    => 10,
    "exec_mode"    => "fork_mode",
];

foreach ($cloud as $cloudName => $multiplier) {
    $args          = $argsTemplate;
    $args['cloud'] = $cloudName;
    
    $appConfig              = $appConfigTemplate;
    $appConfig['name']      = $appConfig['name']. '_' . $version . '_' . $cloudName ;
    $appConfig['args']      = escapeshellarg(http_build_query($args));
    $appConfig['instances'] = ceil($multiplier * $performance);
    
    $result[] = $appConfig;
    
    g_log('Config for cloud "' . $cloudName . '" for version ' . $version.' generated.');
}

$configDir = __DIR__ . '/config';
if (!file_exists($configDir)) {
    mkdir($configDir);
}

$config     = json_encode(['apps' => $result,], JSON_PRETTY_PRINT);
$configFile = $configDir.'/cluster_' . $version . '.json';
file_put_contents('config_file', $configFile);
file_put_contents($configFile, $config);

if (file_exists('config.json')) {
    unlink('config.json');
}

link($configFile, 'config.json');
g_log('Config file config.json write success');
