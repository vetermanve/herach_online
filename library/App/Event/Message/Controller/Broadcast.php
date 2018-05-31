<?php


namespace App\Event\Message\Controller;

use App\Rest\Run\EventControllerProto;
use Load\Load;
use Mu\Env;

class Broadcast extends EventControllerProto
{
    public function processEvent()
    {
        $load = new Load('rest/platform-clients');
        
        $loader = Env::getLoader();
        $loader->addLoad($load);
        $loader->processLoad();
        
        $devices = $load->getResults();
        
        return $devices;
    }
}