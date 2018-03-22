<?php


namespace App\Rest\User\Controller;


use App\Rest\Auth\Storage\SessionStorage;
use App\Rest\Run\RestControllerProto;
use App\Rest\User\Lib\Storage\UserStorage;
use Run\Spec\HttpResponseSpec;
use Uuid\Uuid;

class User extends RestControllerProto
{
    public function get()
    {
        $storage = new UserStorage();
        
        if ($id = $this->p('id')) {
            $user = $storage->read()->get($id, __METHOD__);
            unset($user[UserStorage::SOLT],$user[UserStorage::PASSWORD]);
            return $user;
        }
        
        $users = $storage->search()->find([], 1000, __METHOD__);
        
        foreach ($users as &$user) {
            unset($user[UserStorage::SOLT],$user[UserStorage::PASSWORD]);
        }
        
        return $users;
    }
    
    
    public function post()
    {
        $nickname = $this->p('login', $this->p('nickname'));
        $password = $this->p('password');
        $password2 = $this->p('password_repeat');
    
        $nickname = mb_ereg_replace('[^A-Za-z0-9\.\-]','', $nickname);
    
        if ($password !== $password2) {
            throw new \Exception('Passwords not match', HttpResponseSpec::HTTP_CODE_BAD_REQUEST);
        }
        
        if (!$nickname || !$password) {
            throw new \Exception('Name and password required', HttpResponseSpec::HTTP_CODE_BAD_REQUEST);
        }
    
        $password = md5($password);
        
        $storage = new UserStorage();
        
        $userId = Uuid::v4();
        $sid = $this->getState('sid');
        
        if (!$sid) {
            $sid = Uuid::v4();
        }
        
        $this->setState('sid', $sid);
        
        $solt = hash('whirlpool', mt_rand(1,1000000).mt_rand(1,1000000).mt_rand(1,1000000));
        
        $res = $storage->write()->insert($userId, [
            'nickname' => $nickname,
            'solt'     => $solt,
            'password' => hash('whirlpool', $password.$solt.$nickname),
        ], __METHOD__);
        
        $session = new SessionStorage();
        $session->write()->insert($sid, [
            'user_id' => $userId,
        ], __METHOD__);
        
        return $res;
    }
}