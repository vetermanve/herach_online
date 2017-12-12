<?php


namespace App\Rest\Auth\Controller;


use App\Rest\Auth\Storage\SessionStorage;
use App\Rest\Run\RestControllerProto;
use Uuid\Uuid;

class Session extends RestControllerProto
{
    public function get()
    {
        $id = $this->p('id');
        
        $storage = new SessionStorage();
        $session = $storage->read()->get($id, __METHOD__);
        
        return $session;
    }
    
    public function post()
    {
        $login = $this->p('login');
        $password = $this->p('password');
        $userId = (md5($login) === $password) ? current(explode('.', $login)) : 0;
        
        $id = Uuid::v4();
        $session = [
            'id' => $id,
            'user_id' => $userId,
        ];
    
        $storage = new SessionStorage();
        $storage->write()->insert($id, $session, __METHOD__);
        
        return $session;
    }
    
}