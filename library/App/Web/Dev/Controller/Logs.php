<?php

namespace App\Web\Dev\Controller;

use App\Web\Run\WebControllerProto;
use Mu\Env;

class Logs extends WebControllerProto
{
    public function index () 
    {
        $id = $this->p('id');
        $data = Env::getRedis()->hgetall('slog:'.$id);
        ksort($data);
        return $this->render(['logs' => $data,]);
    }
}