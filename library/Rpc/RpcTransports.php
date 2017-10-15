<?php

namespace Rpc;

class RpcTransports
{
    const PREFIX = 'rpc';
    
    public function getQueueName ($controller, $module, $service = RpcServices::SERVICE_UNIVERSE, $type = RpcServices::TYPE_CONTROLLER) 
    {
        $controller = strtolower($controller);
        $module = strtolower($module);
    
        return self::PREFIX . '.' . $service . '.'  . $module . '.' . $type . '.' . $controller;
    }
    
    public function getOldQueueName ($serviceName) 
    {
        return self::PREFIX.'.service.'.strtolower($serviceName);
    }
    
//    public function getOldServiceConnectionInfo ($serviceName) 
//    {
//        $config = Env::getServiceContainer()->getServiceConfig($serviceName);
//        if ($config) {
//            return [$config['host'], $config['port']];       
//        }
//        
//        return null;
//    }
}