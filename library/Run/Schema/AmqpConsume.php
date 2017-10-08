<?php


namespace Run\Schema;


use iConto\Env;
use Run\Channel\AmqpReplyChannel;
use Run\Component\MainDependencyManager;
use Run\Component\UnexpectedShutdownHandler;
use Run\Processor\AlolRestRequestProcessor;
use Run\Processor\MultiAppHttpProcessor;
use Run\Provider\HttpAmqpCloud;
use Run\Rest\ModuleContainer;
use Run\RuntimeLog;
use Run\Util\UnexpectedEndHandler;

class AmqpConsume extends RunSchemaProto
{
    public function configure()
    {
        $this->core->addComponent(new UnexpectedShutdownHandler());
        $this->core->addComponent(new MainDependencyManager());
        
        $this->core->setProvider(new HttpAmqpCloud());
        $this->core->setProcessor(new MultiAppHttpProcessor());
        $this->core->setDataChannel(new AmqpReplyChannel());
    }
}