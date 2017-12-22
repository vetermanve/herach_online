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
        return null;   
    }
    
    public function post () 
    {
        return null;
    }
    
    public function put () 
    {
        return null;
    }
    
    public function delete () 
    {
        return null;
    }
    
    public function view ()
    {
        return $this->get();   
    }
    
    /**
     * @return RestRequestOptions
     */
    final public function getRequest(): RestRequestOptions
    {
        return $this->request;
    }
    
    /**
     * @param RestRequestOptions $request
     */
    final public function setRequest(RestRequestOptions $request)
    {
        $this->request = $request;
    }
    
    final public function p($name, $default = null)
    {
        return $this->request->getParam($name, $default);
    }
    
    public function getState ($name, $default = null) 
    {
        return $this->request->getState()->get($name, $default);
    }
    
    public function setState ($name, $value, $ttl = null) 
    {
        $this->request->getState()->set($name, $value, $ttl);
    }
}