<?php


namespace App\Rest\Projects\Storage;


use App\Rest\Storage\DataAdapterFactory;
use Storage\SimpleStorage;
use Storage\StorageContext;
use Storage\StorageDependency;

class ProjectStorage extends SimpleStorage
{
    const ID = 'id';
    
    const F_ID    = 'id';
    const F_TITLE = 'title';
    const F_DESC  = 'desc';
    
    public function loadConfig()
    {
        $this->context->set(StorageContext::RESOURCE, 'project-info');
    }
    
    public function customizeDi(StorageDependency $container, StorageContext $context)
    {
        $container->setModule(StorageDependency::DATA_ADAPTER, (new DataAdapterFactory())->getAdapter($context));
    }
}