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
            return $storage->read()->get($id, __METHOD__);
        }
        
        $users = $storage->search()->find([], 1000);
        return $users;
    }
    
    public function post()
    {
        $nickname = $this->p('login');
        $password = $this->p('password');
    
        $nickname = mb_ereg_replace('[^A-Za-z0-9\.\-]','', $nickname);
        
        if (!$nickname || !$password) {
            throw new \Exception('Name and password required', HttpResponseSpec::HTTP_CODE_BAD_REQUEST);
        }
        
        $storage = new UserStorage();
        
        $userId = Uuid::v4();
        $sid = $this->getState('sid', Uuid::v4());
        
        $res = $storage->write()->insert($userId, [
            'id'       => $userId,
            'nickname' => $nickname,
        ], __METHOD__);
        
        $session = new SessionStorage();
        $session->write()->insert($sid, [
            'user_id' => $userId,
        ], __METHOD__);
        
        return $res;
    }
}