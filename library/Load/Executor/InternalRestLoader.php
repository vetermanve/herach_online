<?php

namespace Load\Executor;

use App\Base\Run\BaseRunProcessor;
use App\Base\Run\Component\MainDependencyManager;
use Load\InternalLoadRunProvider;
use Mu\Env;
use Run\Channel\MemoryStoreChannelStack;
use Run\RunContext;
use Run\RunCore;
use Run\RuntimeLog;
use Run\Spec\HttpResponseSpec;

class InternalRestLoader extends LoadExecutorProto
{
    /**
     * @var RunCore
     */
    private $run;
    
    /**
     * @var MemoryStoreChannelStack
     */
    private $dataChannel;
    
    /**
     * @var InternalLoadRunProvider
     */
    private $provider;
    
    private $mainContext;
    
    private $executionContext;
    
    public function init()
    {
        if ($this->run) {
            return ;
        }
        
        $this->mainContext = Env::getContainer();
        
        $this->run = new RunCore();
        $this->dataChannel = new MemoryStoreChannelStack();
        $this->provider = new InternalLoadRunProvider();
        
        $context = new RunContext();
        $context->set(RunContext::GLOBAL_CONFIG, Env::getEnvContext()->getData());
        
        $this->run->setContext($context);
        $this->run->setProvider($this->provider);
        $this->run->setProcessor(new BaseRunProcessor());
        $this->run->addComponent(new MainDependencyManager());
        $this->run->setDataChannel($this->dataChannel);
        $this->run->setRuntime(new RuntimeLog('InternalRestLoader'));
        
        $this->run->prepare();
        
        $this->executionContext  = Env::getContainer();
        Env::setContainer($this->mainContext);
    }
    
    public function processLoad()
    {
        if (!$this->loads) {
            return;
        }
        
        if (!$this->run) {
            $this->init();
        }
        
        $this->provider->setLoads($this->loads);
        
        Env::setContainer($this->executionContext);
        $this->run->run();
        Env::setContainer($this->mainContext);
        
        foreach ($this->loads as $load) {
            $result = $this->dataChannel->getMessageByUid($load->getUuid());
            
            if ($result && $result->getCode() == HttpResponseSpec::HTTP_CODE_OK) {
                $load->setResults($result->body);    
            }
        }
        
        $this->loads = [];
    }
}