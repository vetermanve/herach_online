<?php


namespace App\Read\Projects\Controller;


use App\Read\Run\ReadControllerProto;
use Load\Load;
use Mu\Env;

class Projects extends ReadControllerProto
{
    public function read()
    {
        $id = $this->p('id');
        $count = $this->p('count', 9);
        
        $loader = Env::getLoader();
        
        $projectsLoad = new Load('rest/projects');
        $projectsLoad->setParams([
           'id' => $id,
           'count' => $count,
        ]);
        
        $loader->addLoad($projectsLoad);
        $loader->processLoad();
    
        $projects = $projectsLoad->getResults();
        $userIds =  array_filter(array_column($projects, 'owner_id'));
        
        if ($userIds) {
            $usersLoad = new Load('rest/user');
            $usersLoad->setParams([
                'ids' => array_unique($userIds),
            ]);
            
            $loader->addLoad($usersLoad);
        }
    
        $loader->processLoad();
        
        if (isset($usersLoad) && $usersLoad->getResultsCount()) {
            $usersData = array_column($usersLoad->getResults(), null, 'id');
            foreach ($projects as &$project) {
                if(isset($project['owner_id'])) {
                    $project['owner'] = $usersData[$project['owner_id']] ?: null;
                }
            } unset($project);
        }
        
        return $projects;
    }
}