<?php


namespace App\Rest\User\Lib\Storage;


use App\Rest\Storage\DataAdapterFactory;
use Storage\SimpleStorage;
use Storage\StorageContext;
use Storage\StorageDependency;

class UserStorage extends SimpleStorage
{
    
    public function loadConfig()
    {
        $this->context->set(StorageContext::RESOURCE, 'user-profile');
    }
    
    public function customizeDi(StorageDependency $container, StorageContext $context)
    {
        $container->setModule(StorageDependency::DATA_ADAPTER, (new DataAdapterFactory())->getAdapter($context));
    }
}