<?php

namespace Statist\Loader;

abstract class AbstractLoader {
    
    protected $cache = [];
    
    protected $processor;
    
    function __construct($processor)
    {
        $this->processor = $processor;
    }
    
    abstract function doLoad($keys);
    
    public function getData($keys) 
    {
        $keys = array_unique($keys);
        $idx = array_flip($keys);
        $data = $this->cache ?  array_intersect_key($this->cache, $idx) : [];
    
        if(count($data) != count($keys)) {
            $newData = $this->doLoad(array_diff($keys, array_keys($data)));
            $data += $newData;
            $this->cache += $newData;
        }
        
        return $data;
    }
}
