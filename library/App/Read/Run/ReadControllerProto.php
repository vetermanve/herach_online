<?php


namespace App\Read\Run;


use App\Base\Run\BaseControllerProto;
use App\Rest\Auth\Lib\SessionLoader;

abstract class ReadControllerProto extends BaseControllerProto
{
    abstract public function read();
    
    public function run () 
    {
        return $this->read();
    }
    
    public function validateMethod () 
    {
        return true;
    }
    
    public function _getCurrentUserId ()
    {
        $sid = $this->getState('sid');
        if (!$sid) {
            return 0;
        }
        
        $res = (new SessionLoader())->getSession($sid);
        return isset($res['user_id']) ? $res['user_id'] : 0;
    }
}