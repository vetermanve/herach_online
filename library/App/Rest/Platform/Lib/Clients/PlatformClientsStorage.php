<?php


namespace App\Rest\Platform\Lib\Clients;


use App\Rest\Storage\DataAdapterFactory;
use Storage\SimpleStorage;
use Storage\StorageContext;
use Storage\StorageDependency;

class PlatformClientsStorage extends SimpleStorage
{
    const ID         = 'id';
    const USER_ID    = 'user_id';
    const TYPE       = 'type';
    const OWNER_ID   = 'owner_id';
    const OWNER_TYPE = 'owner_type';
    const ADDRESS    = 'address';
    const KEY        = 'key';
    const SALT       = 'salt';
    const VERSION    = 'version';
    const FEATURES   = 'features';
    
    public function loadConfig()
    {
        $this->context->set(StorageContext::RESOURCE, 'platform-clients');
    }
    
    public function customizeDi(StorageDependency $container, StorageContext $context)
    {
        $container->setModule(StorageDependency::DATA_ADAPTER, (new DataAdapterFactory())->getAdapter($context));
    }
}