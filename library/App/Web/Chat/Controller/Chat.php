<?php


namespace App\Web\Chat\Controller;


use App\Web\Run\WebControllerProto;

class Chat extends WebControllerProto
{
    public function index () 
    {
        return $this->render([]);
    }
}