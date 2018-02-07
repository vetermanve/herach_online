<?php


namespace App\Web\User\Controller;


use App\Web\Run\WebControllerProto;
use Load\Load;
use Mu\Env;

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
        $currentUserId = $session['user_id'] ?? 0;
        
        if (!$currentUserId) {
            throw new \Exception("Not authorised", 401);
        }
        
        $loader = Env::getLoader();
        
        // load user
        {
            $userLoad = new Load('user');
            $userLoad->setParams([
                'id' => $currentUserId,
            ]);
            
            $loader->addLoad($userLoad);
        }
        
        // load user projects
        {
            $projectsLoad = new Load('projects');
            $projectsLoad->setParams([
                'owner_id' => $currentUserId, 
                'count' => 6,
            ]);
            
            $loader->addLoad($projectsLoad);
        }
        
        $loader->init();
        $loader->processLoad();
            
        $user = $userLoad->getResults();
            
        return $this->render([
            'user' => $user,
            'user_id' => $session['user_id'],
            'projects' => $projectsLoad->getResults(),
        ]);
    }
    
    public function list () 
    {
        $userLoad = new Load('user');
        $this->load($userLoad);
    
        $user = $userLoad->getResults();
    
        return $this->render([
            'users' => $user,
        ]);
    }
}