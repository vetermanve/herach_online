<?php

namespace App\Web\Projects\Controller;

use App\Web\Run\WebControllerProto;

class Projects extends WebControllerProto
{
    public function edit () 
    {
        return $this->render([]);
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
        
        return $this->render($data, 'Projects/edit');
    }
    
    public function save () 
    {
        return $this->render([], 'Projects/edit');
    }
}