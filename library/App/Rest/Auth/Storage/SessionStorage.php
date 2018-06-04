<?php


namespace App\Rest\Auth\Storage;


use App\Base\Storage\DataAdapterFactory;
use Storage\SimpleStorage;
use Storage\StorageContext;
use Storage\StorageDependency;

class SessionStorage extends SimpleStorage
{
    const USER_ID = 'user_id'; 
    
    public function loadConfig()
    {
        $this->context->set(StorageContext::RESOURCE, 'session');
    }
    
    public function customizeDi(StorageDependency $container, StorageContext $context)
    {
        $container->setModule(StorageDependency::DATA_ADAPTER, (new DataAdapterFactory())->getAdapter($context));
    }
}