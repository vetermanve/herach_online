<?php


namespace Storage\WriteModule;


use Storage\Request\StorageDataRequest;
use Storage\StorageDataAccessModuleProto;

class AsyncWriteModule  extends StorageDataAccessModuleProto implements WriteModuleInterface
{
    /**
     * @param $id
     * @param $bind
     * @param $callerMethod
     *
     * @return StorageDataRequest
     */
    public function insert($id, $bind, $callerMethod)
    {
        $timer = $this->profiler->openTimer(__METHOD__, $bind, $callerMethod);
        $request = $this->dataAdapter->getInsertRequest($id, $bind);
        $request->send();
        $this->profiler->finishTimer($timer);
        
        return $request;
    }
}