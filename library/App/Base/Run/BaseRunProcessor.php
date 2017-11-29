<?php

namespace App\Base\Run;

use App\Base\Run\Logger\LogSessionHandler;
use Mu\Env;
use Run\Processor\RunRequestProcessorProto;

class BaseRunProcessor extends RunRequestProcessorProto
{
    /**
     * @var RunRequestProcessorProto[] 
     */
    private $processors = [];
    
    /**
     * @var LogSessionHandler
     */
    private $sessionHandler;
    
    public function prepare()
    {
    
        $this->sessionHandler = new LogSessionHandler(Env::getRedis()->getInstance());
        $this->runtime->pushHandler($this->sessionHandler);
    }
    
    public function process(\Run\RunRequest $request)
    {
        $start = microtime(1)*1000;
        $type = $request->getMeta(\Run\Spec\HttpRequestMetaSpec::PROVIDER_TYPE);
        $this->getProcessor($type)->process($request);
        $time = (round((microtime(1)*1000 - $start), 1));
        $this->runtime->runtime('RUN_REQ_END' , $request->params + ['time_ms' => $time, 'resource' => $request->getResource(), 'request_id' => $request->getUid(),]);
        $this->sessionHandler->flushLogs('slog:'.$request->getUid());    }
    
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