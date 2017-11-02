<?php

namespace Load\Executor;

use Load\Load;

abstract class LoadExecutorProto
{
    protected $loads = [];
    
    /**
     * @param Load $load
     *
     * @return $this
     */
    public function addLoad (Load $load) 
    {
        $this->loads[] = $load;
        
        return $this;
    }
    
    abstract public function processLoad();
    
    abstract public function init();
}