<?php


namespace Storage\WriteModule;


interface WriteModuleInterface
{
    public function configure();
    
    public function insert($bind, $callerMethod);
}