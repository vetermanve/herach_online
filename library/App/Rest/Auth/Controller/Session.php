<?php


namespace App\Rest\Auth\Controller;


use App\Rest\Auth\Lib\SessionLoader;
use App\Rest\Auth\Storage\SessionStorage;
use App\Rest\Run\RestControllerProto;
use Uuid\Uuid;

class Session extends RestControllerProto
{
    public function get()
    {
        $id = $this->p('id');
        
        return (new SessionLoader())->getSession($id);
    }
    
    public function post()
    {
        $login = $this->p('login');
        $password = $this->p('password');
        $userId = ('me.'.$login === $password) && is_numeric($login) ? (int)$login : 0;
        
        $sid = Uuid::v4();
        $session = [
            'id' => $sid,
            'user_id' => $userId,
        ];
        
        if ($userId) {
            $this->setState('sid', $sid);
            $this->setState('uid', $userId);
        }
    
        $storage = new SessionStorage();
        $storage->write()->insert($sid, $session, __METHOD__);
        
        return $session;
    }
    
}