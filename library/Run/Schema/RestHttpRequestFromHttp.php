<?php


namespace Run\Schema;

use iConto\Env;
use Run\Channel\JsonHttpResponseChannel;
use Run\Component\MainDependencyManager;
use Run\Component\UnexpectedShutdownHandler;
use Run\Processor\AlolRestRequestProcessor;
use Run\Processor\MultiAppHttpProcessor;
use Run\Provider\PhpFpmRequest;
use Run\Rest\ModuleContainer;
use Run\RunContext;
use Run\RuntimeLog;
use Run\Util\HttpEnvContext;

class RestHttpRequestFromHttp extends RunSchemaProto
{
    /**
     * @var HttpEnvContext
     */
    private $httpEnv;
    
    public function configure()
    {
        $provider = new PhpFpmRequest();
        $provider->setHttpEnv($this->httpEnv);
    
        $this->core->addComponent(new UnexpectedShutdownHandler());
//        $this->core->addComponent(new MainDependencyManager());
        
        $this->core->setProvider($provider);
        $this->core->setProcessor(new MultiAppHttpProcessor());
        $this->core->setDataChannel(new JsonHttpResponseChannel());
    }
    
    /**
     * @param HttpEnvContext $httpEnv
     */
    public function setHttpEnv($httpEnv)
    {
        $this->httpEnv = $httpEnv;
    }
}