<?php


namespace App\Event\Message\Controller;

use App\Event\Message\Lib\BroadcastMessageStorage;
use App\Event\Run\EventControllerProto;
use Load\Load;
use Mu\Env;
use Uuid\Uuid;

class Broadcast extends EventControllerProto
{
    const CLIENT_ID         = 'id';
    const CLIENT_USER_ID    = 'user_id';
    const CLIENT_TYPE       = 'type';
    const CLIENT_OWNER_ID   = 'owner_id';
    const CLIENT_OWNER_TYPE = 'owner_type';
    const CLIENT_ADDRESS    = 'address';
    
    public function processEvent()
    {
        $text = $this->p('message', '');
        
        $load = new Load('rest/platform-clients');
        $load->setParams([
            'limit' => 100, 
        ]);
        
        $loader = Env::getLoader();
        $loader->addLoad($load);
        $loader->processLoad();
        
        $clients = $load->getResults();
    
        $storage = new BroadcastMessageStorage();
        $storageWriter = $storage->write();
        
        $results = [];
        foreach ($clients as $client) {
            $results[] = $storageWriter->insert(Uuid::v4(), [
                BroadcastMessageStorage::ADDRESS   => $client[self::CLIENT_ADDRESS],
                BroadcastMessageStorage::DEVICE_ID => $client[self::CLIENT_ID],
                BroadcastMessageStorage::MESSAGE   => $text . " " . $client[self::CLIENT_ID],
            ], __METHOD__);
        }
        
        return [
            $text,
            "Sent to: ". count($clients).' clients',
            $results
        ];
    }
}