<?php

namespace App\Base\Run;

use Run\Processor\RunRequestProcessorProto;

class BaseRunProcessor extends RunRequestProcessorProto
{
    /**
     * @var RunRequestProcessorProto[] 
     */
    private $processors = [];
    
    public function prepare()
    {
        
    }
    
    public function process(\Run\RunRequest $request)
    {
        $type = $request->getMeta(\Run\Spec\HttpRequestMetaSpec::PROVIDER_TYPE);
        $this->getProcessor($type)->process($request);
    }
    
    /**
     * @param $type
     *
     * @return RunRequestProcessorProto
     */
    public function getProcessor ($type) 
    {
        $type = $type === 'rest' ? 'rest' : 'web'; 
        
        if (isset($this->processors[$type])) {
            return $this->processors[$type];
        }
        
        switch ($type) {
            case 'rest': 
                $processor = new \App\Rest\Run\RestInternalProcessor();
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