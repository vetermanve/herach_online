<?php

namespace App\Web\Landing\Controller;

use App\Web\Run\WebControllerProto;
use Renderer\MutantRenderer;

class Landing extends WebControllerProto
{
    public function index () 
    {
        return $this->render(['feel' => 'good']);
    }
}