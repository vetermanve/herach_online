<?php


namespace Storage\WriteModule;


use Storage\Data\DataAdapterInterface;
use Storage\StorageDependency;
use Storage\StorageModuleProto;
use Storage\StorageProfiler;

class SimpleWriteModule extends StorageModuleProto implements WriteModuleInterface
{
    /**
     * @var DataAdapterInterface
     */
    protected $dataAdapter;
    
    /**
     * @var StorageProfiler
     */
    protected $profiler;
    
    
    public function configure () 
    {
        $this->dataAdapter = $this->diContainer->bootstrap(StorageDependency::DATA_ADAPTER);
        $this->profiler = $this->diContainer->bootstrap(StorageDependency::PROFILER);
    }
    
    /**
     * @param $bind
     * @param $callerMethod
     *
     * @return array|bool|null
     *
     */
    public function insert ($id, $bind, $callerMethod) 
    {
        $timer = $this->profiler->openTimer(__METHOD__, $bind, $callerMethod);
        $request = $this->dataAdapter->getInsertRequest($id, $bind);
        $request->send();
        $request->fetch();
        $this->profiler->finishTimer($timer);
        
        return $request->getResult();
    }
}