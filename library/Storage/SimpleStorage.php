<?php


namespace Storage;


use Storage\Data\SynthesizeUniverseDataAdapter;
use Storage\ReadModule\SimpleReadModule;
use Storage\WriteModule\SimpleWriteModule;

abstract class SimpleStorage extends StorageProto
{
    public function setupDi()
    {
        $context = $this->context;
        $container = $this->diContainer;
        
        $container->setModule(StorageDependency::DATA_ADAPTER, function () use ($context) {
            $adapter = new SynthesizeUniverseDataAdapter();
            $adapter->setModule    ($context->get(StorageContext::UNIVERSE_MODULE));
            $adapter->setController($context->get(StorageContext::UNIVERSE_CONTROLLER));
            $adapter->setModel     ($context->get(StorageContext::UNIVERSE_MODEL));
            $adapter->setTimeout   ($context->get(StorageContext::RPC_TIMEOUT));
            $adapter->setService   ($context->get(StorageContext::RPC_SERVICE));
            $adapter->setType      ($context->get(StorageContext::RPC_TYPE));
            
            return $adapter;
        });
    
        $container->setModule(StorageDependency::WRITE_MODULE, function () use ($container, $context) {
            $module = new SimpleWriteModule();
            $module->setDiContainer($container);
            $module->setContext($context);
            $module->configure();
            return $module;
        });
    
        $container->setModule(StorageDependency::READ_MODULE, function () use ($container, $context) {
            $module = new SimpleReadModule();
            $module->setDiContainer($container);
            $module->setContext($context);
            $module->configure();
            return $module;
        });
    }
}