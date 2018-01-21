<?php


namespace App\Rest\Auth\Controller;


use App\Rest\Auth\Lib\SessionLoader;
use App\Rest\Auth\Storage\SessionStorage;
use App\Rest\Run\RestControllerProto;
use App\Rest\User\Lib\Storage\UserStorage;
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
        $login = trim($this->p('login'));
        $password = $this->p('password');
        
        $password = md5($password);
        
        $storage = new UserStorage();
        $user = $storage->search()->findOne([
             UserStorage::NICKNAME => $login
        ], __METHOD__);
        
        if (!$user) {
            return [
                'error' => 'no user found',
            ];
        }
        
        $passwordHash = hash('whirlpool', $password.$user[UserStorage::SOLT].$login);
        
        if ($passwordHash !== $user[UserStorage::PASSWORD]) {
            return null;    
        }
        
        $sid = $this->getState('sid', Uuid::v4());
        $userId = $user[UserStorage::ID];
        
        $sessionInsert = [
            'user_id' => $userId,
        ];
        
        $storage = new SessionStorage();
        $session = $storage->write()->insert($sid, $sessionInsert, __METHOD__);
        
        $this->setState('sid', $sid);
        $this->setState('uid', $userId);
        
        return $session;
    }
    
    public function delete () 
    {
        $sid = $this->getState('sid');
        
        $this->setState('sid', null);
        $this->setState('uid', null);
    
        $storage = new SessionStorage();
        $results = $storage->write()->remove($sid, __METHOD__);
        
        return $results;
    }
}