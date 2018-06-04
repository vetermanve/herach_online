<?php

namespace App\Dev\Dev\Controller;

use App\Base\Storage\LogStorage;
use App\Web\Run\WebControllerProto;

class Logs extends WebControllerProto
{
    public function index () 
    {
        $storage = new LogStorage();
        $id = $this->p('id');
        $data = $storage->read()->get($id, __METHOD__);
        return $this->render(['logs' => $data,]);
    }
}