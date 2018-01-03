<?php


namespace Storage\WriteModule;


use Storage\StorageDataAccessModuleProto;

class SimpleWriteModule extends StorageDataAccessModuleProto implements WriteModuleInterface
{
    /**
     * @param $bind
     * @param $callerMethod
     *
     * @return array|bool|null
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