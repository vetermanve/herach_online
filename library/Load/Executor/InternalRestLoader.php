<?php

namespace Load\Executor;

use App\Rest\Run\RestInternalProcessor;
use Mu\Env;
use Run\Channel\MemoryStoreChannelStack;
use Run\RunContext;
use Run\RunCore;
use Run\RunRequest;
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
    
    public function processLoad()
    {
        foreach ($this->loads as $load) {
            $request = new RunRequest($load->getUuid(), $load->getResource());
            $request->params = $load->getParams();
            
            $this->run->process($request);
            $result = $this->dataChannel->getMessageByUid($load->getUuid());
            
            if ($result && $result->getCode() == HttpResponseSpec::HTTP_CODE_OK) {
                $load->setResults($result->body);    
            }
        }
        
        $this->loads = [];
    }
    
    public function init()
    {
        $this->run = new RunCore();
        $this->dataChannel = new MemoryStoreChannelStack();
        
        $this->run->setContext(new RunContext());
        $this->run->setProcessor(new RestInternalProcessor());
        $this->run->setDataChannel($this->dataChannel);
        $this->run->setRuntime(new RuntimeLog('InternalRestLoader'));
        
        $this->run->prepare();
    }
}