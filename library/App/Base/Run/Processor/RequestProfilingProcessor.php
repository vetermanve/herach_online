<?php


namespace App\Base\Run\Processor;


use App\Base\Run\Logger\StorageRequestLogsHandler;
use App\Base\Run\Logger\LogHandlerInterface;
use App\Base\Storage\LogStorage;
use Run\Processor\RunRequestProcessorProto;
use Run\RunRequest;

class RequestProfilingProcessor extends RunRequestProcessorProto
{
    /**
     * @var LogHandlerInterface
     */
    private $requestLogHandler;
    
    public function prepare()
    {
        try {
            $this->requestLogHandler = new StorageRequestLogsHandler(function () {
                return new LogStorage();
            });
        } catch (\Throwable $exception) {
            trigger_error("Can't create sessionHandler: ".$exception->getMessage(), E_USER_WARNING);
        }
    }
    
    public function process(RunRequest $request, RunRequestProcessorProto $processor = null)
    {
        $start = microtime(1)*1000;
    
        // if we initialised correctly
        if ($this->requestLogHandler) {
            // pus our log handler
            $this->runtime->pushHandler($this->requestLogHandler);
            
            try {
                // run request on target processor
                $processor->process($request);
                // get processing time
                $time = (round((microtime(1)*1000 - $start), 1));
                // write logs about processing time and request params
                $this->runtime->runtime('RUN_REQ_END' , $request->params + ['time_ms' => $time, 'resource' => $request->getResource(), 'request_id' => $request->getUid(),]);
                // pop out session handler
                $this->runtime->popHandler();
                // flush logs
                $this->requestLogHandler->flushLogs($request->getUid());
            } catch (\Exception $exception) {
                $this->runtime->error("We had an exception:".$exception->getMessage());
                // pop out session handler
                $this->runtime->popHandler();
                // flush logs
                $this->requestLogHandler->flushLogs($request->getUid());
                
                throw $exception;
            }
            
        } else {
            $processor->process($request);
            trigger_error("Request log handler is missing!", E_USER_WARNING);
        }
    }
}