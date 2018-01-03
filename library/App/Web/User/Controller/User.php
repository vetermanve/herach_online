<?php


namespace App\Web\User\Controller;


use App\Web\Run\WebControllerProto;
use Load\Load;

class User extends WebControllerProto
{
    public function index () 
    {
        $authLoad = new Load('auth-session');
        $authLoad->setParams([
            'id' => $this->getState('sid'),
        ]);
        
        $this->load($authLoad);
        
        $session = $authLoad->getResults();
        
        if (!$session) {
            return 'Not authorised';
        }
        
        $userLoad = new Load('user');
        $userLoad->setParams([
            'id' => $session['user_id'],
        ]);
        
        $this->load($userLoad);
            
        $user = $userLoad->getResults();
            
        return $this->render([
            'user' => $user,
            'user_id' => $session['user_id'],
        ]);
    }
}