<?php


namespace Run\Component;

use Load\Executor\InternalRestLoader;
use Mu\Amqp\Connection;
use Mu\Application;
use Mu\Cache;
use Mu\Env;
use Router\Router;
use Mu\Logger;
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
        
        $container->setModule('logger', $this->runtime);
    
        $config = new ConfigModule();
        $config->follow($this);
        $config->loadDataFromContext(RunContext::GLOBAL_CONFIG);
        
        $container->setModule('config', $config);
        
        $container->setModule('events', function () {
            return new EventDispatcher(); 
        });
    
        $container->setModule('loader', function () {
            $loader = new InternalRestLoader();
            $loader->init();
            return $loader;
        });
    
        $container->setModule('redis', function () use ($config) {
            $params = [
                'host'               => $config->get('host', 'redis', 'localhost'),
                'port'               => $config->get('port', 'redis', '6379'),
                'password'           => $config->get('password', 'redis', false),
                'connection_timeout' => $config->get('connection_timeout', 'redis', 3),
                'read_timeout'       => $config->get('read_timeout', 'redis', 3)
            ];
        
            return new Cache\Redis($params);
        });
    
        $container->setModule('router', function () use ($config) {
            $router = new Router();
            return $router->init($config);
        });
    
        $container->setModule('logger', $this->runtime);
        
//        {
//            $loggerConfig = [
//                'name'       => $this->context->get(RunContext::IDENTITY, 'unknown@dev'),
//                'handlers'   => [
//                    new \Monolog\Handler\GelfHandler(new \Gelf\MessagePublisher('graylog', 12201, \Gelf\MessagePublisher::CHUNK_SIZE_LAN)),
//                ],
//                'processors' => [
//                    new \Monolog\Processor\PsrLogMessageProcessor()
//                ],
//            ];
//    
//            $remoteLogger = new Logger(new ConfigModule($loggerConfig));
//            $this->runtime->setRemoteLogger($remoteLogger);
//        }
        
    }
}