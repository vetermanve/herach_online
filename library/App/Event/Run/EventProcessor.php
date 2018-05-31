<?php


namespace App\Event\Run;


use App\Base\Run\RestProcessorProto;

class EventProcessor extends RestProcessorProto
{
    public function getAppName()
    {
        return 'Event';
    }
}