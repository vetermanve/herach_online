<?php


namespace App\Rest\Projects\Storage;


use App\Base\Storage\DataAdapterFactory;
use Storage\SimpleStorage;
use Storage\StorageContext;
use Storage\StorageDependency;

class ProjectActorsStorage extends SimpleStorage
{
    const ID = 'id';
    
    const F_PROJECT_ID    = 'p_id';
    const F_USER_ID       = 'u_id';
    const F_STATUS        = 'status';
    const F_INVITED_BY_ID = 'inv_id';
    const F_ADDED         = 'add_date';
    
    public function loadConfig()
    {
        $this->context->set(StorageContext::RESOURCE, 'project-actors');
    }
    
    public function customizeDi(StorageDependency $container, StorageContext $context)
    {
        $container->setModule(StorageDependency::DATA_ADAPTER, (new DataAdapterFactory())->getAdapter($context));
    }
}