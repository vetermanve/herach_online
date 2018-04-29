<?php

namespace App\Base\Run;

use App\Base\Run\Logger\FileSystemHandler;
use App\Base\Run\Logger\LogHandlerInterface;
use Run\Processor\RunRequestProcessorProto;
use Run\RunContext;
use Run\Storage\LogStorage;

class BaseRunProcessor extends RunRequestProcessorProto
{
    /**
     * @var RunRequestProcessorProto[] 
     */
    private $processors = [];
    
    /**
     * @var LogHandlerInterface
     */
    private $sessionHandler;
    
    private function _prepareSessionHandler() {
        if ($this->sessionHandler) {
            return;
        }
    
        try {
            $this->sessionHandler = new FileSystemHandler(function () {
                return new LogStorage();
            });
            $this->runtime->pushHandler($this->sessionHandler);
        } catch (\Throwable $exception) {
            trigger_error("Can't create sessionHandler: ".$exception->getMessage(), E_USER_WARNING);
        }
    }
    
    public function prepare()
    {

    }
    
    public function process(\Run\RunRequest $request)
    {
        $isDebugRuntime = (bool)$request->getParamOrData('_debug') || $this->context->getEnv(RunContext::ENV_DEBUG); 
        if ($isDebugRuntime) {
            $this->_prepareSessionHandler(); 
        }
        
        $start = microtime(1)*1000;
        $type = $request->getMeta(\Run\Spec\HttpRequestMetaSpec::PROVIDER_TYPE);
        $this->getProcessor($type)->process($request);
        $time = (round((microtime(1)*1000 - $start), 1));
        $this->runtime->runtime('RUN_REQ_END' , $request->params + ['time_ms' => $time, 'resource' => $request->getResource(), 'request_id' => $request->getUid(),]);
    
        if ($isDebugRuntime) {
            $this->sessionHandler && $this->sessionHandler->flushLogs('slog:'.$request->getUid());
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