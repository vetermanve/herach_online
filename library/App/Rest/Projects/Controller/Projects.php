<?php


namespace App\Rest\Projects\Controller;


use App\Rest\Run\RestControllerProto;
use Database\DatabaseFactory;

class Projects extends RestControllerProto
{
    public function get () 
    {
        $count = $this->p('count', 1);
        
        $dbFactory = new DatabaseFactory();
        $table = $dbFactory->getDatabaseByResource('project_info');
        $data = $table->getAll();
        
        foreach ($data as &$item) {
            if (isset($item['name'])) {
                $item['title'] = $item['name'];    
            }
        } unset($item);
        
        return $data;
    }
}