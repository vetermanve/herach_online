<?php


namespace App\Base\Run\Schema;


use App\Base\Run\BaseRunProcessor;
use App\Base\Run\Component\MainDependencyManager;
use Run\Channel\AmqpReplyChannel;
use Run\Component\UnexpectedShutdownHandler;
use Run\Provider\HttpAmqpCloud;
use Run\Schema\RunSchemaProto;

class AmqpConsume extends RunSchemaProto
{
    public function configure()
    {
        $this->core->addComponent(new UnexpectedShutdownHandler());
        $this->core->addComponent(new MainDependencyManager());
        
        $this->core->setProvider(new HttpAmqpCloud());
        $this->core->setProcessor(new BaseRunProcessor());
        $this->core->setDataChannel(new AmqpReplyChannel());
    }
}