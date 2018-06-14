<?php


namespace App\Rest\Chat\Controller;


use App\Rest\Run\RestControllerProto;
use App\Rest\Chat\Lib\Storage\ChatMessagesStorage;
use Load\Load;
use Mu\Env;
use Uuid\Uuid;

class Messages extends RestControllerProto
{
    public function get()
    {
        $storage = new ChatMessagesStorage();
        
        return $storage->search()->find([], 100, __METHOD__, [
            'sort' => [['created', 'asc']],
        ]);
    }
    
    public function post()
    {
        $text = trim($this->p('text', ''));
        if (!$text) {
            throw new \Exception("Empty text", 409);
        }
        
        $userId = $this->_getCurrentUserId();
        if (!$userId) {
            throw new \Exception("Unauthorised", 401);
        }
        
        $storage = new ChatMessagesStorage();
        
        $bind = [
            ChatMessagesStorage::AUTHOR_ID => $userId,
            ChatMessagesStorage::TEXT      => $text,
            ChatMessagesStorage::CREATED   => time()
        ];
        
        $result = $storage->write()->insert(Uuid::v4(), $bind, __METHOD__);
        
        if ($result) {
            $event = new Load('event/chat-message');
            $event->setParams([
                'type'    => 'created',
                'payload' => $result
            ]);
            
            Env::getLoader()->addLoad($event)->processLoad();
            
            Env::getLogger()->info(__METHOD__." : event sent!", $event->getResults());
        }
        
        return $result;
    }
}