<?php


namespace Storage\WriteModule;


interface WriteModuleInterface
{
    public function configure();
    
    public function insert($id, $bind, $callerMethod);
    public function remove($id, $callerMethod);
    public function update ($id, $bind, $callerMethod);
}