<?php


namespace App\Rest\Auth\Lib;


use App\Rest\Auth\Storage\SessionStorage;

class SessionLoader
{
    public function getSession ($id) 
    {
        $storage = new SessionStorage();
        return $storage->read()->get($id, __METHOD__) ?? [];
    }
}