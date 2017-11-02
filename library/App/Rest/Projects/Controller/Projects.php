<?php


namespace App\Rest\Projects\Controller;


use App\Rest\Run\RestControllerProto;

class Projects extends RestControllerProto
{
    public function get () 
    {
        $count = $this->p('count', 1);
        
        $result = [];
        foreach (range(1, $count) as $id) {
            $result[] = [
                'id' => $id,
                'title' => 'Sample Project #1',
            ];
        }
        
        return $result;
    }
}