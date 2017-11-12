<?php


namespace Run\Schema;


use App\Base\Run\BaseRunProcessor;
use Run\Channel\AmqpReplyChannel;
use Run\Component\MainDependencyManager;
use Run\Component\UnexpectedShutdownHandler;
use Run\Provider\HttpAmqpCloud;

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