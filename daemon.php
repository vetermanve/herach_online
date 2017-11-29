<?php

include_once 'bootstrap.php';

use App\Base\Run\Schema\AmqpConsume;
use Run\RunContext;
use Run\RunCore;
use Run\RuntimeLog;

if (isset($argv[1])) {
    parse_str($argv[1], $config);    
} else {
    $config = [];
}

$namespace  = 'bpass';
$dataCenter = isset($config['dc']) ? $config['dc'] : 'dc';
$cloud      = isset($config['cloud']) ? $config['cloud'] : 'all';
$host       = isset($config['host']) ? $config['host'] : null;

$incomingQueue = $namespace . '.' . $dataCenter . '.' . $cloud;


$identity = 'd.' . getmypid() . '@' . gethostname();

if (file_exists('release.json')) {
    $releaseData = json_decode(file_get_contents('release.json'), 1);
    if (isset($releaseData['id'])) {
        $identity = $releaseData['id'].'.'.$identity;
    }
}

$context = new RunContext();
$context->fill([
    RunContext::AMQP_REQUEST_CLOUD_HOST => $host,
    RunContext::AMQP_RESULT_CLOUD_HOST  => $host,
    RunContext::QUEUE_INCOMING          => $incomingQueue,
    RunContext::IDENTITY                => $identity
]);

$context->set(RunContext::REQUEST_PROFILING_ENABLED, 1);

//$context->set(RunContext::GLOBAL_CONFIG, parse_ini_file('conf/core.ini', true));
$context->set(RunContext::GLOBAL_CONFIG, [
    'amqp' => [
        'host' => 'localhost',
        'port' => '5672',
    ],
//    'db' => [
//        'port' => '55432',
//    ],
]);

if (!$host && $amqpConfig = $context->getScope(RunContext::GLOBAL_CONFIG, 'amqp')) {
    $context->set(RunContext::AMQP_REQUEST_CLOUD_HOST, $amqpConfig['host']); 
    $context->set(RunContext::AMQP_REQUEST_CLOUD_PORT, $amqpConfig['port']); 
    $context->set(RunContext::AMQP_RESULT_CLOUD_HOST, $amqpConfig['host']); 
}

$runtime = new RuntimeLog($context->get(RunContext::IDENTITY));
$runtime->catchErrors();

$core = new RunCore();
$core->setContext($context);
$core->setSchema(new AmqpConsume());
$core->setRuntime($runtime);
$core->configure();
$core->prepare();
$core->run();
