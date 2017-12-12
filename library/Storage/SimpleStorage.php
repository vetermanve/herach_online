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