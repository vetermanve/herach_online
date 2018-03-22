<?php


namespace App\Rest\Auth\Controller;


use App\Rest\Auth\Lib\SessionLoader;
use App\Rest\Run\RestControllerProto;

class User extends RestControllerProto
{
    public function get()
    {
        $sid = $this->getState('sid');
    
        $session = [];
        
        if ($sid) {
            $session = (new SessionLoader())->getSession($sid);    
        }
        
        return [
            'user_id' => $session['user_id'] ?? 0,
        ];
    }
}