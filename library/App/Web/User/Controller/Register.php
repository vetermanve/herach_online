<?php


namespace App\Web\User\Controller;


use App\Web\Run\WebControllerProto;
use Load\Load;

class Register extends WebControllerProto
{
    public function index () 
    {
        $loader = new Load('auth-session');
        $loader->setParams([
            'id' => $this->getState('sid'), 
        ]);
        $this->load($loader);
        
        if ($session = $loader->getResults()) {
            return $this->render([
                'session' => $session,
            ], 'already_registered');
        }
        
        return $this->render([]);
    }
}