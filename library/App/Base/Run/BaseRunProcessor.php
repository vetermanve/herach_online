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
     * @var RunRequestProcessorProto[]|callable[]
     */
    private $processors = [];
    
    /**
     * @var RequestProfilingProcessor
     */
    private $profilingProcessor;
    
    const PROCESSOR_DEFAULT = self::PROCESSOR_WEB;
    
    const PROCESSOR_REST  = 'rest';
    const PROCESSOR_READ  = 'read';
    const PROCESSOR_EVENT = 'event';
    const PROCESSOR_DEV   = 'dev';
    const PROCESSOR_WEB   = 'web';
    
    public function prepare()
    {
        $this->processors = [
            self::PROCESSOR_REST  => function () {
                return new \App\Rest\Run\RestProcessor();
            },
            self::PROCESSOR_READ  => function () {
                return new \App\Read\Run\ReadProcessor();
            },
            self::PROCESSOR_EVENT => function () {
                return new \App\Event\Run\EventProcessor();
            },
            self::PROCESSOR_DEV   => function () {
                return new \App\Dev\Run\DevProcessor();
            },
            self::PROCESSOR_WEB   => function () {
                return new \App\Web\Run\WebProcessor();
            }
        ];
    }
    
    /**
     * @return RequestProfilingProcessor
     */
    private function _getProfilingProcessor()
    {
        if (!$this->profilingProcessor) {
            $this->profilingProcessor = new RequestProfilingProcessor();
            $this->profilingProcessor->follow($this);
            $this->profilingProcessor->prepare();
        }
        
        return $this->profilingProcessor;
    }
    
    public function process(RunRequest $request)
    {
        $resourceParts = explode('/', trim($request->getResource(),'/'));
        $type = $resourceParts[0];
        $processor = $this->getProcessor($type);
        
        if ($processor) {
            // remove processor prefix;
            array_shift($resourceParts);
            $request->setResource('/'.implode('/', $resourceParts));
        } else {
            // get default processor
            $processor = $this->getProcessor(self::PROCESSOR_DEFAULT); 
        }
        
        // compose params to check if we should profile this request 
        {
            $debugParam         = (bool)$request->getParamOrData('_debug');
            $debugContext       = $this->context->getEnv(RunContext::ENV_DEBUG);
            $debugShowProcessor = $type === 'dev';
        }
        
        $isDebugAllowed = ($debugParam || $debugContext) && !$debugShowProcessor;
        
        if ($isDebugAllowed) {
            $this->_getProfilingProcessor()->process($request, $processor);
        } else {
            $processor->process($request);
        }
    }
    
    /**
     * @param $type
     *
     * @return RunRequestProcessorProto
     */
    public function getProcessor($type)
    {
        if (!isset($this->processors[$type])) {
            return null;
        }
        
        if (is_callable($this->processors[$type])) {
            /* @var $processor RunRequestProcessorProto */
            $processor = $this->processors[$type]();
            $processor->follow($this);
            $processor->prepare();
            $this->processors[$type] = $processor;
        }
        
        return $this->processors[$type];
    }
}