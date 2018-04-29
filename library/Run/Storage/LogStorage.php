<?php

namespace Run\Storage;

use Storage\SimpleStorage;
use App\Rest\Storage\DataAdapterFactory;
use Storage\StorageContext;
use Storage\StorageDependency;

class LogStorage extends SimpleStorage
{
    const RESOURCE_LOG = 'log';

    public function loadConfig()
    {
        $this->context->set(StorageContext::RESOURCE, self::RESOURCE_LOG);
    }

    public function customizeDi(StorageDependency $container, StorageContext $context)
    {
        $container->setModule(StorageDependency::DATA_ADAPTER, (new DataAdapterFactory())->getAdapter($context));
    }
}