<?php

namespace App\Web\Run;

use App\Base\Run\WebProcessorProto;

class WebProcessor extends WebProcessorProto
{
    public function getAppName()
    {
        return "Web";
    }
}