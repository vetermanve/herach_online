<?php


namespace App\Base\Storage;

use Mu\Env;
use Storage\Data\JBaseDataAdapter;
use Storage\Data\RabbitRouterDataAdapter;
use Storage\StorageContext;

class DataAdapterFactory
{
    const DB_NAME       = 'database';
    const DB_TYPE       = 'type';
    const DB_CONNECTION = 'connection';
    
    const RABBIT_QUEUE_KEY = 'queue_key';
    
    const TYPE_JBASE = 'jbase';
    const TYPE_RABBIT = 'rabbit';
    const TYPE_POSTGRES_JSON = 'postgres_json';
    
    public function getAdapter (StorageContext $context) 
    {
        $type = $context->get(StorageContext::TYPE, 'db');
        $scope = $context->get(StorageContext::SCOPE, 'default');
        
        $config = Env::getEnvContext()->getScope($type, $scope, [
            self::DB_TYPE       => self::TYPE_JBASE,
            self::DB_NAME       => 'default',
            self::DB_CONNECTION => '/tmp',
        ]);
        
        Env::getLogger()->info(__METHOD__, [$context->get(StorageContext::RESOURCE), $type, $scope, $config]);
        
        switch ($config[self::DB_TYPE]) {
            case self::TYPE_JBASE :
                return function (StorageContext $context) use ($config) {
                    /* @var StorageContext $context */
                    $adapter = new JBaseDataAdapter();
                    $adapter->setResource($context->get(StorageContext::RESOURCE));
                    $adapter->setDatabase($config[self::DB_NAME]);
                    $adapter->setDataRoot($config[self::DB_CONNECTION]);
        
                    return $adapter;
                };
    
            case self::TYPE_RABBIT :
                return function (StorageContext $context) use ($config) {
                    /* @var StorageContext $context */
                    $adapter = new RabbitRouterDataAdapter();
                    $adapter->setQueueKey($context->get(self::RABBIT_QUEUE_KEY));
                    $adapter->setResource($context->get(StorageContext::RESOURCE));
                    return $adapter;
                };
            case self::TYPE_POSTGRES_JSON : 
                throw new \Exception("NIY");
            default:
                throw new \Exception("Adapter not found");
        }
    }
}