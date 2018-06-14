<?php


namespace App\Event\Message\Lib;


use App\Base\Storage\DataAdapterFactory;
use Storage\SimpleStorage;
use Storage\StorageContext;
use Storage\StorageDependency;

class BroadcastMessageStorage extends SimpleStorage
{
    const ADDRESS   = 'address';
    const DEVICE_ID = 'device_id';
    const DATA      = 'data';
    const TYPE      = 'type';
    
    public function loadConfig()
    {
        $this->context->set(StorageContext::RESOURCE, 'event-message-broadcast');
        $this->context->set(StorageContext::TYPE, 'event');
        $this->context->set(DataAdapterFactory::RABBIT_QUEUE_KEY, self::ADDRESS);
    }
    
    public function customizeDi(StorageDependency $container, StorageContext $context)
    {
        $container->setModule(StorageDependency::DATA_ADAPTER, (new DataAdapterFactory())->getAdapter($context));
    }
}