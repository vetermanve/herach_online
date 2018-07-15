<?php

namespace App\Web\Projects\Controller;

use App\Web\Run\WebControllerProto;
use Load\Load;
use Mu\Env;

class Projects extends WebControllerProto
{
    public function edit()
    {
        $id     = $this->p('id');
        $userId = $this->_getCurrentUserId();
        
        $load = new Load('rest/projects');
        $load->setParams([
            'id'    => $id,
            'count' => 1,
        ]);
        
        $this->load($load);
        
        $projectData = $load->getFirstResult([]);
        if (!$projectData) {
            throw new \Exception("Not found", 404);
        }
        
        if (!isset($projectData['owner_id']) || $projectData['owner_id'] !== $userId) {
            throw new \Exception("Wrong access rights", 403);
        }
        
        return $this->render([
            'project' => $projectData,
        ], __FUNCTION__);
    }
    
    public function index()
    {
        $id = $this->p('id');
        if ($id) {
            return $this->show();
        }
        
        $count = $this->p('count', 100);
        
        $projects = new Load('read/projects');
        $projects->setParams([
            'count' => $count,
        ]);
        
        $this->load($projects);
        
        return $this->render(['projects' => $projects->getResults()]);
    }
    
    public function show()
    {
        $id     = $this->p('id');
        $userId = $this->_getCurrentUserId();
        
        $loader = Env::getLoader();
        
        $loadProject = new Load('rest/projects');
        $loadProject->setParams([
            'id'    => $id,
            'count' => 1,
        ]);
        
        $loadActors = new Load('rest/projects-actors');
        $loadActors->setParams([
            'p_id'  => $id,
            'limit' => 12,
        ]);
        
        $loader->addLoad($loadProject);
        $loader->addLoad($loadActors);
        
        $loader->processLoad();
        
        $projectData = $loadProject->getFirstResult([]);
        if (!$projectData) {
            throw new \Exception("Project not found", 404);
        }
        
        $ownerId = $projectData['owner_id'] ?? null;
        if (!$ownerId) {
            throw new \Exception("Corrupted project", 422);
        }
        
        $actors  = $loadActors->getResults();
        $userIds = array_column($actors, 'u_id', 'u_id');
        
        $userIds[$ownerId] = $ownerId;
        
        $usersLoad = new Load('rest/user');
        $usersLoad->setParams([
            'ids' => $userIds,
        ]);
        
        $loader->addLoad($usersLoad)->processLoad();
        
        return $this->render([
            'project'  => $projectData,
            'users'    => $usersLoad->getResults(),
            'editable' => isset($projectData['owner_id']) ? $projectData['owner_id'] === $userId : true
        ], __FUNCTION__);
    }
    
    /**
     * новый проект
     * @return
     */
    public function project()
    {
        $data = [
            'project' => [
                'title' => '',
            ],
        ];
        
        return $this->render($data, 'edit');
    }
    
    public function save()
    {
        return $this->render([], 'edit');
    }
    
    public function _getTemplateDir()
    {
        return dirname(__DIR__) . '/Template/' . (new \ReflectionClass(get_class()))->getShortName();
    }
}