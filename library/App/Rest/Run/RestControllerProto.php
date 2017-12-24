<?php

namespace App\Rest\Run;

use App\Base\Run\BaseControllerProto;

abstract class RestControllerProto extends BaseControllerProto
{
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