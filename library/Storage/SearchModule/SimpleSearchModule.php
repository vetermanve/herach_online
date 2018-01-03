<?php


namespace Storage\SearchModule;


use Storage\StorageDataAccessModuleProto;

class SimpleSearchModule extends StorageDataAccessModuleProto implements SearchModuleInterface
{
    
    public function find($filters, $limit, $meta = [], $caller = null)
    {
        $t = $this->profiler->openTimer(__METHOD__, json_encode($filters), $caller);
        $request = $this->dataAdapter->getSearchRequest($filters, $limit, $meta);
        $request->send();
        $result = $request->fetch();
        $this->profiler->finishTimer($t);
        return $result;
    }
    
    public function findOne($filters, $meta = [])
    {
        $data = $this->find($filters, 1, $meta);
        return $data ? reset($data) : null;
    }
}