<?php

namespace App\Base\Run;

use App\Base\Run\Processor\RequestProfilingProcessor;
use Run\Processor\RunRequestProcessorProto;
use Run\RunContext;
use Run\RunRequest;
use Run\Spec\HttpRequestMetaSpec;
use Run\Util\HttpResourceHelper;

class BaseRunProcessor extends RunRequestProcessorProto
{
    /**
     * @var RunRequestProcessorProto[] 
     */
    private $processors = [];
    
    /**
     * @var RequestProfilingProcessor
     */
    private $profilingProcessor;
    
    public function prepare()
    {
        $this->profilingProcessor = new RequestProfilingProcessor();
        $this->profilingProcessor->follow($this);
        $this->profilingProcessor->prepare();
    }
    
    public function process(RunRequest $request)
    {
        $pathData = new HttpResourceHelper($request->getResource());
        $type = $pathData->getType();
        // get processor for provider
        $processor = $this->getProcessor($type);
        
        // compose params to check if we should profile this request 
        {
            $debugParam = (bool)$request->getParamOrData('_debug');
            $debugContext = $this->context->getEnv(RunContext::ENV_DEBUG);
            $debugShowProcessor = $type === 'dev';
        }
        
        $isDebugAllowed = ($debugParam || $debugContext) && !$debugShowProcessor; 
            
        if ($isDebugAllowed) {
            $this->profilingProcessor->process($request, $processor); 
         } else {
            $processor->process($request);
        }
    }
    
    /**
     * @param $type
     *
     * @return RunRequestProcessorProto
     */
    public function getProcessor ($type) 
    {
        if (isset($this->processors[$type])) {
            return $this->processors[$type];
        }
        
        switch ($type) {
            case 'rest': 
                $processor = new \App\Rest\Run\RestProcessor();
                break;
            case 'read':
                $processor = new \App\Read\Run\ReadProcessor();
                break;
            case 'dev':
                $processor = new \App\Dev\Run\DevProcessor();
                break;
            case 'web':
            default:
                $processor = new \App\Web\Run\WebProcessor();
        }
        
        $processor->follow($this);
        $processor->prepare();
        
        $this->processors[$type] = $processor;
        return $processor;
    }
}