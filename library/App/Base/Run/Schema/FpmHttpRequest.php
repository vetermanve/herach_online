<?php


namespace App\Base\Run\Schema;

use App\Base\Run\BaseRunProcessor;
use App\Base\Run\Component\MainDependencyManager;
use Run\Channel\JsonHttpResponseChannel;
use Run\Component\UnexpectedShutdownHandler;
use Run\Provider\PhpFpmRequest;
use Run\Schema\RunSchemaProto;
use Run\Util\HttpEnvContext;

class FpmHttpRequest extends RunSchemaProto
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
        $this->core->addComponent(new MainDependencyManager());
        
        $this->core->setProvider($provider);
        $this->core->setProcessor(new BaseRunProcessor());
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