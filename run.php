<?php
chdir(__DIR__);

require_once(__DIR__.'/_util/get_all_headers.php');
require_once(__DIR__.'/bootstrap.php');

use Run\Util\HttpEnvContext;
use Run\RunContext;
use Run\RunCore;
use Run\Schema\RestHttpRequestFromHttp;

$env = new HttpEnvContext();
$env->fill([
    HttpEnvContext::HTTP_COOKIE    => &$_COOKIE,
    HttpEnvContext::HTTP_GET       => &$_GET,
    HttpEnvContext::HTTP_POST      => &$_POST,
    HttpEnvContext::HTTP_POST_BODY => trim(file_get_contents("php://input")),
    HttpEnvContext::HTTP_SERVER    => &$_SERVER,
    HttpEnvContext::HTTP_HEADERS   => getallheaders(),
]);

$schema = new RestHttpRequestFromHttp();
$schema->setHttpEnv($env);

$context = new RunContext();
$context->fill([
    RunContext::HOST     => $_SERVER['HTTP_HOST'],
    RunContext::IDENTITY => ('fpm.'.getmypid() . '@' . gethostname()),
    RunContext::IS_SECURE_CONNECTION => stripos($_SERVER['SERVER_PROTOCOL'],'https') === true
]);

$configFile = 'conf/core.ini';

$context->setKeyActivation(RunContext::GLOBAL_CONFIG, function () use ($configFile) {
    if (file_exists($configFile)) {
        return parse_ini_file($configFile, true);    
    }
     
    return [];
});

$runtime = new \Run\RuntimeLog($context->get(RunContext::IDENTITY));
$runtime->catchErrors();

$core = new RunCore();
$core->setContext($context);
$core->setSchema($schema);
$core->setRuntime($runtime);

return $core;
