<?php


namespace Storage;


class StorageDependency
{
    const WRITE_MODULE  = 'write';
    const READ_MODULE   = 'read';
    const SEARCH_MODULE = 'search';
    const CACHE_MODULE  = 'cache';
    
    const PROFILER      = 'profiler';
    
    const DATA_ADAPTER  = 'data';
    
    private $modules = [];
    
    public function bootstrap($module, $required = true)
    {
        if (!isset($this->modules[$module])) {
            if ($required) {
                throw new \Exception('Module '.$module.' not supported');
            } else {
                return null;
            }
        }
        
        if (is_callable($this->modules[$module])) {
            $this->modules[$module] = $this->modules[$module]();
        }
        
        return $this->modules[$module];
    }
    
    public function setModule($moduleName, $module)
    {
        return $this->modules[$moduleName] = $module;
    }
}