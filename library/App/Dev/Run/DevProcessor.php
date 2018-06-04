<?php


namespace App\Dev\Run;

use App\Base\Run\WebProcessorProto;

class DevProcessor extends WebProcessorProto
{
    public function getAppName()
    {
        return 'Dev';
    }
}