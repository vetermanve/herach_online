<?php


namespace App\Rest\Registration\Controller;


use App\Rest\Run\RestControllerProto;
use App\Rest\User\Lib\Registration\Channel\TelegramBotChannel;
use App\Rest\User\Lib\Registration\ContactConfirmStorage;

class Registration extends RestControllerProto
{
    public function get () 
    {
        $storage = new ContactConfirmStorage();;
        
        $bot = new TelegramBotChannel();
        $bot->setStorage($storage);
        $bot->readAll();
        $bot->save();
        
        $sid = $this->getState('sid');
        
    }
    
    public function post()
    {
        $sid = $this->getState('sid');
        $code = mt_rand(10000, 99999);
        
        $storage = new ContactConfirmStorage();
        
        $confirm = $storage->write()->insert($sid, [
            [
                'code' => $code,
                'confirmed' => false,
            ]
        ], __METHOD__);
        
        return $confirm;
    }
}