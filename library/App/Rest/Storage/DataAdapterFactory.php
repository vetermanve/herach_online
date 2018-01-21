<?php


namespace App\Rest\Storage;


use App\Rest\Storage\DataAdapter\JBaseDataAdapter;
use Mu\Env;
use Storage\StorageContext;

class DataAdapterFactory
{
    const DB_NAME       = 'database';
    const DB_TYPE       = 'type';
    const DB_CONNECTION = 'connection';
    
    const TYPE_JBASE = 'jbase';
    const TYPE_POSTGRES_JSON = 'postgres_json';
    
    public function getAdapter (StorageContext $context) 
    {
        $config = Env::getEnvContext()->getScope('db', 'default', [
            self::DB_TYPE       => 'jbase',
            self::DB_NAME       => 'default',
            self::DB_CONNECTION => '/tmp',
        ]);
        
        Env::getLogger()->info(__METHOD__, $config);
        
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
            case self::TYPE_POSTGRES_JSON : 
                throw new \Exception("NIY");
            default:
                throw new \Exception("Adapter not found");
        }
    }
}