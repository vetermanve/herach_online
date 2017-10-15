<?php


namespace Run\Processor;


use App\Evotor\Run\EvotorRequestProcessor;
use App\Oauth2\Run\Oauth2RequestProcessor;
use Mu\Env;
use Mu\Logger;
use Run\RunRequest;

class MultiAppHttpProcessor extends RunRequestProcessorProto
{
    const PROCESSOR_EVOTOR = 'evotor';
    const PROCESSOR_ALOL   = 'alol';
    const PROCESSOR_OAUTH  = 'oauth';
    
    /**
     * @var RunRequestProcessorProto[]
     */
    private $processors = [];
    
    public function prepare()
    {
        
    }
    
    public function process(RunRequest $request)
    {
        $type = $this->_getProcessorType($request);
        
        $processor = isset($this->processors[$type]) ? $this->processors[$type] : null;
            
        if (!$processor) {
            $processor = $this->_getProcessor($type);
            $this->processors[$type] = $processor;
        }
    
        $this->runtime->info('RUN_HTTP_REQUEST', 
            [
                'resource' => $request->getResource(),
                'params' => $request->params,
            ] 
            + ($request->meta ?: []) 
            + $request->getChannelState()->getData()
        );
        
        $processor->process($request);
    }
    
    /**
     * @param $type
     *
     * @return RunRequestProcessorProto
     */
    private function _getProcessor($type) {
        $processor = null;
        
        switch ($type) {
            case self::PROCESSOR_EVOTOR:
                $processor = new EvotorRequestProcessor();
                break;
    
            case self::PROCESSOR_OAUTH:
                $processor = new Oauth2RequestProcessor();
                break;
            
            case self::PROCESSOR_ALOL:
            default: 
                $processor = new AlolRestRequestProcessor();
                break;
        }
        
        $processor->follow($this);
        $processor->prepare();
        
        return $processor;
    }
    
    private function _getProcessorType(RunRequest $runRequest)
    {
        $resource = $runRequest->getResource();
        $resourceParts = explode('-', strtolower($resource));
        $module = array_shift($resourceParts);
        $component = $resourceParts ? implode('-', $resourceParts) : $module;
        
        if ($module === 'evotor' && $component !== 'auth') {
            return self::PROCESSOR_EVOTOR;
        }
    
        if ($module === 'oauth2') {
            return self::PROCESSOR_OAUTH;
        }
        
        return self::PROCESSOR_ALOL;
    }
}