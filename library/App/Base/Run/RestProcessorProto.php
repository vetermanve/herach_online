<?php


namespace App\Base\Run;


use Run\ChannelMessage\ChannelMsg;
use Run\ChannelMessage\HttpReply;
use Run\Processor\RunRequestProcessorProto;
use Run\Rest\RestRequestOptions;
use Run\RunRequest;
use Run\Spec\HttpRequestMetaSpec;
use Run\Spec\HttpResponseSpec;

abstract class RestProcessorProto extends BaseRoutedProcessor
{
    public function _getRequestMethod (RunRequest $request)
    {
        return $request->getMeta(HttpRequestMetaSpec::REQUEST_METHOD, 'get');
    }
    
    public function process(RunRequest $request)
    {
        $request->meta[HttpRequestMetaSpec::EXECUTION_START] = microtime(1);
        
        $response = $this->_buildResponseObject($request);
        $response->setHeader(HttpResponseSpec::META_HTTP_HEADER_CONTENT_TYPE, HttpResponseSpec::CONTENT_JSON);
        
        try {
            $controller = $this->_getControllerClass($request);
            
            if (!$controller || !($controller instanceof BaseControllerProto)) {
                return $this->abnormalResponse(
                    HttpResponseSpec::HTTP_CODE_NOT_FOUND,
                    'Incorrect resource',
                    $response,
                    $request
                );
            }
    
            $options = new RestRequestOptions();
            $options->setRequest($request);
            $controller->setRequestOptions($options);
    
            $method = $this->_getRequestMethod($request);
            $controller->setMethod($method);
            
            if (!$controller->validateMethod()) {
                return $this->abnormalResponse(
                    HttpResponseSpec::HTTP_CODE_NOT_FOUND,
                    'Incorrect resource',
                    $response,
                    $request
                );
            }
            
            $response->setCode(HttpResponseSpec::HTTP_CODE_OK);
            $response->setBody($controller->run());
            
        } catch (\Throwable $throwable) {
            // possible is an http code
            if ($throwable->getCode() >= 300 && $throwable->getCode() < 600) {
                $response->setCode($throwable->getCode());
                $response->body = [
                    'message' => $throwable->getMessage(),
                    'code' => $throwable->getCode(),
                ];
            } else {
                $response->setCode(HttpResponseSpec::HTTP_CODE_ERROR);
                $response->body = 'Internal error : '. $throwable->getMessage().' on '.$throwable->getTraceAsString();
            }
        }
        
        $this->sendResponse($response, $request);
    }
    
    protected function abnormalResponse(int $code, string $text, ChannelMsg $response, RunRequest $request) {
        $response->setCode($code);
        $response->body = $text;
        $this->sendResponse($response, $request);
    }
    
    public function sendResponse(ChannelMsg $response, RunRequest $request)
    {
        $response->setMeta(HttpResponseSpec::META_EXECUTION_TIME, microtime(true) - $request->getMeta(HttpRequestMetaSpec::EXECUTION_START));
        return parent::sendResponse($response, $request);
    }
}