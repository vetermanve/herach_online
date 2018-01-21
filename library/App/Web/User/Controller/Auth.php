<?php


namespace App\Web\User\Controller;


use App\Web\Run\WebControllerProto;
use Mu\Env;

class Auth extends WebControllerProto
{
    public function index () 
    {
        $authUser = $this->_getCurrentUserId();
        
        return $this->render([
            'is_auth' => (bool)$authUser,
        ]);
    }
}