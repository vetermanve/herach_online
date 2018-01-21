<?php

namespace Mu;

use Modular\ModularContextProto;
use Mu\Interfaces\ContainerInterface;
use Mu\Interfaces\SessionInterface;
use Monolog\Logger;

class Env
{
    /**
     * @var ContainerInterface
     */
    private static $container;
    
    private static $isProfiling;
    
    private static $isDebug;
    
    /**
     * @param bool $autoInit
     *
     * @return ContainerInterface
     * @throws \Exception
     */
    public static function getContainer() {
        return self::$container;
    }
    
    /**
     * @return bool
     */
    public static function isDebugMode()
    {
        self::$isDebug === null && self::$isDebug = (bool)filter_var(self::getEnvContext()->getScope('error', 'debug'), FILTER_VALIDATE_BOOLEAN);
        return self::$isDebug;
    }

    public static function isProfiling()
    {
        self::$isProfiling === null && self::$isProfiling = self::isDebugMode() || self::getEnvContext()->getScope('error', 'profiling'); 
        return self::$isProfiling;
    }

    /**
     * @return \Mu\Interfaces\ConfigInterface
     */
    public static function getLegacyConfig()
    {
        return self::getContainer()->bootstrap('config');
    }
    
    /**
     * @return ModularContextProto
     */
    public static function getEnvContext()
    {
        return self::getContainer()->bootstrap('env_context');
    }
    
    
    /**
     * @return \Mu\Cache\Redis
     */
    public static function getRedis()
    {
        return self::getContainer()->bootstrap('redis');
    }

    /**
     * @return Logger
     */
    public static function getLogger()
    {
        return self::getContainer()->bootstrap('logger');
    }
    
    /**
     * @param bool $required
     *
     * @return SessionInterface
     */
    public static function getSession($required = true)
    {
        return self::getContainer()->bootstrap('session', $required);
    }
    
    /**
     * @return \Router\Router
     */
    public static function getRouter()
    {
        return self::getContainer()->bootstrap('router');
    }
    
    /**
     * @return \Run\Event\EventDispatcher
     */
    public static function getEventDispatcher()
    {
        return self::getContainer()->bootstrap('events');
    }
    
    /**
     * @return \Renderer\MuRenderer
     */
    public static function getRenderer()
    {
        return self::getContainer()->bootstrap('renderer');
    }
    
    /**
     * @return \Load\Executor\LoadExecutorProto
     */
    public static function getLoader()
    {
        return self::getContainer()->bootstrap('loader');
    }
    
    /**
     * @param ContainerInterface $container
     */
    public static function setContainer(ContainerInterface $container)
    {
        self::$container = $container;
    }
    
    
}
