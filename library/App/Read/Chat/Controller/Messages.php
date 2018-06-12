<?php


namespace App\Read\Chat\Controller;


use App\Read\Run\ReadControllerProto;
use Load\Load;
use Mu\Env;

class Messages extends ReadControllerProto
{
    
    public function read()
    {
        $loader = Env::getLoader();
        
        $messagesLoad = new Load('rest/chat-messages');
        
        $loader->addLoad($messagesLoad)->processLoad();
        
        $messages = $messagesLoad->getResults();
    
        $userIds = [];
        
        foreach ($messages as $id => $message) {
            $userIds[$id] = $message['author_id'];     
        }
        
        $usersLoad = new Load('rest/user');
        $usersLoad->setParams([
            'ids' => array_unique($userIds),
        ]);
    
        $loader->addLoad($usersLoad)->processLoad();
        
        $authors = $usersLoad->getResults();
        
        foreach ($userIds as $messageId => $userId) {
            $messages[$messageId]['author'] = $authors[$userId] ?? [];
            $messages[$messageId]['author_name'] = $authors[$userId]['nickname'] ?? "Unknown";
        }
        
        return $messages;
    }
}