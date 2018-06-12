<?php


namespace App\Rest\Chat\Lib\Storage;


use App\Base\Storage\DataAdapterFactory;
use Storage\SimpleStorage;
use Storage\StorageContext;
use Storage\StorageDependency;

class ChatMessagesStorage extends SimpleStorage
{
    const AUTHOR_ID = 'author_id';
    const TEXT      = 'text';
    const CREATED   = 'created';
    
    public function loadConfig()
    {
        $this->context->set(StorageContext::RESOURCE, 'chat-messages');
    }
    
    public function customizeDi(StorageDependency $container, StorageContext $context)
    {
        $container->setModule(StorageDependency::DATA_ADAPTER, (new DataAdapterFactory())->getAdapter($context));
    }
}