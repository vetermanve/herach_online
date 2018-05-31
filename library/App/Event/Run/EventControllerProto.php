<?php

namespace App\Event\Run;

use App\Base\Run\BaseControllerProto;
use App\Rest\Auth\Lib\SessionLoader;

abstract class EventControllerProto extends BaseControllerProto
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
    
    abstract public function processEvent();
    
    public function run () 
    {
        return $this->processEvent();
    }
    
    public function validateMethod () 
    {
        return true;//method_exists($this, $this->method);
    }
}