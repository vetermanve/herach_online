<?php


namespace Run\Component;

use iConto\Amqp\Connection;
use iConto\Application;
use iConto\Cache;
use iConto\Env;
use Router\Router;
use iConto\Logger;
use Run\Event\EventDispatcher;
use Run\Module\ConfigModule;
use Run\Module\ServicesModule;
use Run\Rest\ModuleContainer;
use Run\RunContext;

class MainDependencyManager extends RunComponentProto
{
    public function run()
    {
        $self = $this;
        
        $container = new ModuleContainer();
        Env::setContainer($container);
        
        $container->setModule(Application::LOGGER, $this->runtime);
    
        $config = new ConfigModule();
        $config->follow($this);
        $config->loadDataFromContext(RunContext::GLOBAL_CONFIG);
        
        $container->setModule(Application::APP_CONFIG, $config);
        
        $container->setModule(Application::EVENT_DISPATCHER, function () {
            return new EventDispatcher(); 
        });
    
        $container->setModule(Application::REDIS, function () use ($config) {
            $params = [
                'host'               => $config->get('host', 'redis', 'localhost'),
                'port'               => $config->get('port', 'redis', '6379'),
                'password'           => $config->get('password', 'redis', false),
                'connection_timeout' => $config->get('connection_timeout', 'redis', 3),
                'read_timeout'       => $config->get('read_timeout', 'redis', 3)
            ];
        
            return Cache::factory(Cache::ADAPTER_TYPE_REDIS, $params);
        });
    
        $container->setModule(Application::SERVICE_CONTAINER, function () use ($self) {
            $serviceContainer = new ServicesModule();
            $serviceContainer->follow($self);
            $serviceContainer->init();
        
            return $serviceContainer;
        });
    
        $container->setModule(Application::CACHE, function () use ($config) {
            return Cache::factory(Cache::ADAPTER_TYPE_MEMCACHED, [
                'host' => $config->get('host', 'memcached', 'localhost'),
                'port' => $config->get('port', 'memcached', '11211')
            ]);
        });
        
        $container->setModule(Application::CACHE_APC, function () {
            return Cache::factory(Cache::ADAPTER_TYPE_APC); 
        });
    
        $container->setModule(Application::QUEUE, function () use ($config) {
            return new Connection ($config->getSection('amqp'));
        });
    
        $container->setModule(Application::ROUTER, function () use ($config) {
            $router = new Router();
            return $router->init($config);
        });
    
        $container->setModule(Application::LOGGER, $this->runtime);
        
        {
            $loggerConfig = [
                'name'       => $this->context->get(RunContext::IDENTITY, 'unknown@dev'),
                'handlers'   => [
                    new \Monolog\Handler\GelfHandler(new \Gelf\MessagePublisher('graylog', 12201, \Gelf\MessagePublisher::CHUNK_SIZE_LAN)),
                ],
                'processors' => [
                    new \Monolog\Processor\PsrLogMessageProcessor()
                ],
            ];
    
            $remoteLogger = new Logger(new ConfigModule($loggerConfig));
            $this->runtime->setRemoteLogger($remoteLogger);
        }
        
        $container->setModule(Application::OAUTH, function () {
            $config = include ('conf/oauth.php');
            return new \Hybrid_Auth($config);
        });
        
        $container->setModule(Application::OAUTH_CONFIG, function () {
            $data = include ('conf/oauth.php');
            return new ConfigModule($data);
        });
    }
}