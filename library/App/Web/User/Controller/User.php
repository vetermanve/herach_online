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
            $projectsOwnedLoad = new Load('rest/projects');
            $projectsOwnedLoad->setParams([
                'owner_id' => $userId,
                'count' => 6,
            ]);
            
            $loader->addLoad($projectsOwnedLoad);
        }
    
        // load projects relations
        {
            $projectsRelation = new Load('rest/projects-actors');
            $projectsRelation->setParams([
                'u_id' => $userId,
                'count' => 6,
            ]);
            
            $loader->addLoad($projectsRelation);
        }
        
        $loader->init();
        $loader->processLoad();
            
        $user = $userLoad->getResults();
        
        if ($projectsRelation->getResultsCount()) {
            $joinedProjectsIds = array_column($projectsRelation->getResults(),'p_id', 'p_id');
    
            $projectsJoinedLoad = new Load('rest/projects');
            $projectsJoinedLoad->setParams([
                'ids' => $joinedProjectsIds,
                'count' => 6,
            ]);
    
            $loader->addLoad($projectsJoinedLoad);
            $loader->processLoad();
        }
        
            
        return $this->render([
            'user' => $user,
            'user_id' => $authUserId,
            'projects' => $projectsOwnedLoad->getResults(),
            'projects_joined' => isset($projectsJoinedLoad) ? $projectsJoinedLoad->getResults() : [],
        ]);
    }
    
    public function list () 
    {
        $userLoad = new Load('rest/user');
        $this->load($userLoad);
    
        $users = $userLoad->getResults();
    
        return $this->render([
            'users' => $users,
        ]);
    }
}