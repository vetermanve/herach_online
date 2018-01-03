<?php

namespace App\Web\Landing\Controller;

use App\Web\Run\WebControllerProto;
use Load\Load;

class Landing extends WebControllerProto
{
    public function index () 
    {
        $count = $this->p('count', 100);
        
        $projects = new Load('projects');
        $projects->setParams([
            'count' => $count, 
        ]);
        
        $this->load($projects);
        
        return $this->render(['projects' => $projects->getResults()]);
    }
}