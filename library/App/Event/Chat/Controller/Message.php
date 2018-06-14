<?php


namespace App\Event\Chat\Controller;


use App\Event\Message\Lib\BroadcastMessageStorage;
use App\Event\Run\EventControllerProto;
use Load\Load;
use Mu\Env;
use Uuid\Uuid;

class Message extends EventControllerProto
{
    const CLIENT_ID         = 'id';
    const CLIENT_USER_ID    = 'user_id';
    const CLIENT_TYPE       = 'type';
    const CLIENT_OWNER_ID   = 'owner_id';
    const CLIENT_OWNER_TYPE = 'owner_type';
    const CLIENT_ADDRESS    = 'address';
    
    const MESSAGE_ID  = 'id';
    const MESSAGE_AUTHOR_ID  = 'author_id';
    const MESSAGE_TEXT  = 'text';
    
    /* 
    {
        "id":"b62eef7f-3c3b-4f21-9764-efdd155bb420",
        "author_id":"7a24d0ad-5efd-4e46-8b84-b092fec82187",
        "text":"123",
        "created":1528896263
    } 
    */
    
    public function processEvent()
    {
        $load = new Load('rest/platform-clients');
        $load->setParams([
            'limit' => 100,
            'last_active' => time() - 3600,
        ]);
        
        $message = $this->p('payload', []);
        if (!$message) {
            throw new \Exception("Message invalid", 409);
        }
    
        $loader = Env::getLoader();
        $loader->addLoad($load);
        $loader->processLoad();
    
        $clients = $load->getResults();
        
        $userIds = array_column($clients, self::CLIENT_OWNER_ID);
    
        $storage = new BroadcastMessageStorage();
        $storageWriter = $storage->write();
    
        $results = [];
        foreach ($clients as $client) {
            // @todo try write batch
            $results[] = $storageWriter->insert(Uuid::v4(), [
                BroadcastMessageStorage::ADDRESS   => $client[self::CLIENT_ADDRESS],
                BroadcastMessageStorage::DEVICE_ID => $client[self::CLIENT_ID],
                BroadcastMessageStorage::DATA      => $message,
                BroadcastMessageStorage::TYPE      => 'chatMessage',
            ], __METHOD__);
    
            $results[] = $storageWriter->insert(Uuid::v4(), [
                BroadcastMessageStorage::ADDRESS   => $client[self::CLIENT_ADDRESS],
                BroadcastMessageStorage::DEVICE_ID => $client[self::CLIENT_ID],
                BroadcastMessageStorage::DATA      => $userIds,
                BroadcastMessageStorage::TYPE      => 'chatUsers',
            ], __METHOD__);
        }
        
        return ["Sent" , $userIds, $clients, $results];   
    }
}