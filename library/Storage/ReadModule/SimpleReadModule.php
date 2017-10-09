<?php


namespace Storage\ReadModule;


use Storage\Data\DataAdapterInterface;
use Storage\Request\StorageDataRequest;
use Storage\StorageDependency;
use Storage\StorageModuleProto;
use Storage\StorageProfiler;

class SimpleReadModule extends StorageModuleProto implements ReadModuleInterface
{
    /**
     * @var DataAdapterInterface
     */
    protected $dataAdapter;
    
    /**
     * @var StorageProfiler
     */
    protected $profiler;
    
    private $idKey = 'id';
    
    private $scopeKey;
    
    public function configure ()
    {
        $this->dataAdapter = $this->diContainer->bootstrap(StorageDependency::DATA_ADAPTER);
        $this->profiler = $this->diContainer->bootstrap(StorageDependency::PROFILER);
    }
    
    public function get($id, $caller, $default = null)
    {
        $t = $this->profiler->openTimer(__METHOD__, $id, $caller);
        $request = $this->dataAdapter->getReadRequest([$id]);
        $request->send();
        $result = $request->fetch();
        $this->profiler->finishTimer($t);
        return $result ? reset($result) : $default;
    }
    
    /**
     * @param       $ids
     * @param       $caller
     * @param array $default
     *
     * @return array
     */
    public function mGet($ids, $caller, $default = [])
    {
        $timer = $this->profiler->openTimer(__METHOD__, $ids, $caller);
        $request = $this->dataAdapter->getReadRequest($ids);
        $request->send();
        $request->fetch();
        $this->profiler->finishTimer($timer);
        
        return $request->hasResult() ? $request->getResult() : $default;
    }
    
    /**
     * @param mixed $dataAdapter
     */
    public function setDataAdapter($dataAdapter)
    {
        $this->dataAdapter = $dataAdapter;
    }
    
    /**
     * @param mixed $scopeKey
     */
    public function setScopeKey($scopeKey)
    {
        $this->scopeKey = $scopeKey;
    }
    

    /**
     * @return string
     */
    public function getIdKey()
    {
        return $this->idKey;
    }
    
    /**
     * @param string $idKey
     */
    public function setIdKey($idKey)
    {
        $this->idKey = $idKey;
    }
}