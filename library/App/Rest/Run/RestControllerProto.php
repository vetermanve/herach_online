<?php

namespace App\Rest\Run;

use Run\Rest\RestRequestOptions;

abstract class RestControllerProto
{
    /**
     * @var RestRequestOptions
     */
    protected $request;
    
    public function get() {
        
    }
    
    public function post () 
    {
        
    }
    
    public function put () 
    {
        
    }
    
    public function delete () 
    {
        
    }
    
    /**
     * @return RestRequestOptions
     */
    public function getRequest(): RestRequestOptions
    {
        return $this->request;
    }
    
    /**
     * @param RestRequestOptions $request
     */
    public function setRequest(RestRequestOptions $request)
    {
        $this->request = $request;
    }
    
    public function p($name, $default = null)
    {
        return $this->request->getParam($name, $default);
    }
}