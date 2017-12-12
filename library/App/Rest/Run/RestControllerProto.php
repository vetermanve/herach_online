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
}