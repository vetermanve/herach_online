<?php

namespace App\Rest\Run;

use App\Base\Run\BaseControllerProto;
use App\Rest\Auth\Lib\SessionLoader;

abstract class RestControllerProto extends BaseControllerProto
{
    public function _getCurrentUserId () 
    {
        $sid = $this->getState('sid');
        if (!$sid) {
            return 0;
        }
        
        $res = (new SessionLoader())->getSession($sid);
        return isset($res['user_id']) ? $res['user_id'] : 0;
    }
    
    public function run () 
    {
        return $this->{$this->method}();
    }
    
    public function validateMethod () 
    {
        return method_exists($this, $this->method);
    }
    
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
}