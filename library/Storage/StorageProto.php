<?php


namespace Storage;


use iConto\Env;
use Storage\ReadModule\ReadModuleInterface;
use Storage\WriteModule\WriteModuleInterface;

abstract class StorageProto extends StorageModuleProto
{
    private $configured = false;
    
    /**
     * StorageProto constructor.
     */
    public function __construct()
    {
        $this->diContainer = new StorageDependency();
        $this->context = new StorageContext();
    }
    
    protected function preConfigure() {
        $this->diContainer->setModule(StorageDependency::PROFILER, function () {
            $profiler = new StorageProfiler();
            if (Env::isProfiling()) {
                $logger = Env::getLogger();
                $profiler->setTimerReportCallback(function ($timer) use ($logger) {
                    $logger->addDebug('StorageProfiler', $timer);
                });
            }
            
            return $profiler;
        });
    }
    
    private function runConfigure() {
        $this->loadConfig();
        $this->preConfigure();
        $this->setupDi();
        $this->customizeDi($this->diContainer, $this->context);
        $this->configured = true;
    }
    
    abstract public function loadConfig();
    abstract public function setupDi();
    abstract public function customizeDi(StorageDependency $container, StorageContext $context);
    
    /**
     * @return StorageProfiler
     */
    public function getProfiler () 
    {
        !$this->configured && $this->runConfigure();
        return $this->diContainer->bootstrap(StorageDependency::PROFILER);
    }
    
    /**
     * @return WriteModuleInterface
     */
    public function write () 
    {
        !$this->configured && $this->runConfigure();
        return $this->diContainer->bootstrap(StorageDependency::WRITE_MODULE);
    }
    
    /**
     * @return WriteModuleInterface
     */
    public function search () 
    {
        !$this->configured && $this->runConfigure();
        return $this->diContainer->bootstrap(StorageDependency::SEARCH_MODULE);
    }
    
    /**
     * @return ReadModuleInterface
     */
    public function read () 
    {
        !$this->configured && $this->runConfigure();
        return $this->diContainer->bootstrap(StorageDependency::READ_MODULE);
    }
    
    /**
     * @return StorageProfiler
     */
    public function profiler () 
    {
        !$this->configured && $this->runConfigure();
        return $this->diContainer->bootstrap(StorageDependency::PROFILER);
    }
}