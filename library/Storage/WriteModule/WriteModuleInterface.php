<?php


namespace Storage\WriteModule;


interface WriteModuleInterface
{
    public function configure();
    
    public function insert($id, $bind, $callerMethod);
}