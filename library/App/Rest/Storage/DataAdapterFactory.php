<?php


namespace App\Rest\Storage;


use App\Rest\Storage\DataAdapter\JBaseDataAdapter;
use Storage\StorageContext;

class DataAdapterFactory
{
    public function getAdapter (StorageContext $context) 
    {
        return function (StorageContext $context) {
            /* @var StorageContext $context */
            $adapter = new JBaseDataAdapter();
            $adapter->setResource($context->get(StorageContext::RESOURCE));
        
            return $adapter;
        };
    }
}