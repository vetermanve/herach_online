<?php


namespace App\Read\Run;


use App\Base\Run\RestProcessorProto;

class ReadProcessor extends RestProcessorProto
{
    
    public function getAppName()
    {
        return "Read";
    }
}