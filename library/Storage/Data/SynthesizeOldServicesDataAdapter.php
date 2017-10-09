<?php


namespace Storage\Data;


use iConto\Rpc\DataRequest;

class SynthesizeOldServicesDataAdapter extends SynthesizeUniverseDataAdapter
{
    protected function getRequest($name)
    {
        $request = new DataRequest();
    
        $request
            ->setService($this->service)
            ->setMethod($name.$this->model)
            ->setTimeout($this->timeout)
        ;
    
        return $request;
    }
}