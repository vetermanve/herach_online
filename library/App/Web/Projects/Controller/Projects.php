<?php

namespace App\Web\Projects\Controller;

use App\Web\Run\WebControllerProto;

class Projects extends WebControllerProto
{
    public function edit () 
    {
        return $this->render([]);
    }
    
    public function save () 
    {
        return $this->render([], 'Projects/edit');
    }
}