<?php


namespace App\Rest\Projects\Controller;


use App\Rest\Projects\Model\Project;
use App\Rest\Run\RestControllerProto;
use Database\DatabaseFactory;

class Projects extends RestControllerProto
{
    public function get () 
    {
        $count = $this->p('count', 1);
        
        $dbFactory = new DatabaseFactory();
        $table = $dbFactory->getDatabaseByResource(Project::DB_TABLE);
        $data = $table->getAll($count);
        
        foreach ($data as &$item) {
            if (isset($item['name'])) {
                $item[Project::F_TITLE] = $item['name'];    
            }
        } unset($item);
        
        return $data;
    }
    
    public function post () 
    {
        $data = [
            Project::F_TITLE => $this->p(Project::F_TITLE),
            Project::F_DESC => $this->p(Project::F_DESC)
        ];
    
        $dbFactory = new DatabaseFactory();
        $table = $dbFactory->getDatabaseByResource(Project::DB_TABLE);
        $result = $table->set(null, $data);
        
        return $result;
    }
    
}