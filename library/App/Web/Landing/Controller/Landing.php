<?php

namespace App\Web\Landing\Controller;

use App\Web\Run\WebControllerProto;
use Load\Load;

class Landing extends WebControllerProto
{
    public function index () 
    {
        $projects = new Load('projects');
        
        $this->load($projects);
        
        return $this->render(['projects' => $projects->getResults()]);
    }
}