<?php


namespace App\Web\User\Controller;


use App\Web\Run\WebControllerProto;
use Load\Load;
use Mu\Env;

class User extends WebControllerProto
{
    public function index () 
    {
        $authLoad = new Load('rest/auth-session');
        $authLoad->setParams([
            'id' => $this->getState('sid'),
        ]);
        
        $this->load($authLoad);
        
        $session = $authLoad->getResults();
        $authUserId = $session['user_id'] ?? 0;
        $userId = $this->p('id');
        if ($userId === 'me') {
            $userId = $authUserId;
        }
    
        if (!$userId) {
            throw new \Exception("Bad Request", 409);
        }
        
        $loader = Env::getLoader();
        
        // load user
        {
            $userLoad = new Load('rest/user');
            $userLoad->setParams([
                'id' => $userId,
            ]);
            
            $loader->addLoad($userLoad);
        }
        
        // load user projects
        {
            $projectsLoad = new Load('rest/projects');
            $projectsLoad->setParams([
                'owner_id' => $userId, 
                'count' => 6,
            ]);
            
            $loader->addLoad($projectsLoad);
        }
        
        $loader->init();
        $loader->processLoad();
            
        $user = $userLoad->getResults();
            
        return $this->render([
            'user' => $user,
            'user_id' => $authUserId,
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