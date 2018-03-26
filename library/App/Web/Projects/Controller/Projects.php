<?php

namespace App\Web\Projects\Controller;

use App\Web\Run\WebControllerProto;
use Load\Load;

class Projects extends WebControllerProto
{
    public function edit () 
    {
        $id = $this->p('id');
        $userId = $this->_getCurrentUserId();
    
        $load = new Load('projects');
        $load->setParams([
            'id' => $id,
            'count' => 1,
        ]);
    
        $this->load($load);
    
        $projectData = $load->getFirstResult([]);
        if (!$projectData) {
            throw new \Exception("Not found", 404);
        }
    
        if ($projectData['owner_id'] !== $userId) {
            throw new \Exception("Wrong access rights", 403);       
        }
    
        return $this->render([
            'project' => $projectData,
        ], __FUNCTION__);
    }
    
    public function index () 
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
    
    public function show () 
    {
        $id = $this->p('id');
        $userId = $this->_getCurrentUserId();
        
        $load = new Load('projects');
        $load->setParams([
            'id' => $id,
            'count' => 1,
        ]);
    
        $this->load($load);
    
        $projectData = $load->getFirstResult([]);
    
        return $this->render([
            'project' => $projectData,
            'editable' => $projectData['owner_id'] === $userId
        ], __FUNCTION__);
    }
    /**
     * новый проект
     * @return 
     */
    public function project () 
    {
        $data = [
            'project' => [
                'title' => 'Новый проект',
            ],
        ];
        
        return $this->render($data, 'edit');
    }
    
    public function save () 
    {
        return $this->render([], 'edit');
    }
    
    public function _getTemplateDir () 
    {
        return dirname(__DIR__).'/Template/'.(new \ReflectionClass(get_class()))->getShortName();
    }
}