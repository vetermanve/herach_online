<?php


namespace App\Web\User\Controller;


use App\Web\Run\WebControllerProto;

class Auth extends WebControllerProto
{
    public function index () 
    {
        return $this->render([]);
    }
}