<?php


namespace App\Read\Run;


use App\Base\Run\BaseControllerProto;

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
}