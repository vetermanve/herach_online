<?php


namespace App\Base\Run;


use Run\ChannelMessage\ChannelMsg;
use Run\ChannelMessage\HttpReply;
use Run\Processor\RunRequestProcessorProto;
use Run\Rest\RestRequestOptions;
use Run\RunRequest;
use Run\Spec\HttpRequestMetaSpec;
use Run\Spec\HttpResponseSpec;

abstract class RestProcessorProto extends RunRequestProcessorProto
{
    private $appName = '';
    
    abstract public function getAppName();
    
    public function prepare()
    {
        $this->appName = $this->getAppName();
    }
    
    /**
     * @param RunRequest $request
     *
     * @return BaseControllerProto
     */
    protected function _getControllerClass(RunRequest $request) {
        $resParts = array_filter(explode('/', $request->getResource()));
        if (isset($resParts[0]) && $resParts[0]) {
            $moduleParts = explode('-', $resParts[0]);
            $moduleName = ucfirst(array_shift($moduleParts));
            if ($moduleParts) {
                array_walk($moduleParts, function (&$val) {
                    $val = ucfirst($val);
                });
                $controllerName = implode('', $moduleParts);
            } else {
                $controllerName = $moduleName;
            }
        } else {
            $moduleName = $controllerName = 'Landing';
        }
        
        $controllerClassName = $this->_getClassName($moduleName, $controllerName);
        
        if (!class_exists($controllerClassName)) {
            return null;
        }
        
        return new $controllerClassName;
    }
    
    protected function _getClassName($moduleName, $controllerName) {
        return '\\App\\'.$this->appName.'\\'.$moduleName.'\\Controller\\'.$controllerName;
    }
    
    public function _buildResponseObject (RunRequest $request)
    {
        $response = new HttpReply();
        $response->setUid($request->getUid());
        $response->setDestination($request->getReply());
        $response->setChannelState($request->getChannelState());
        $response->setHeaders(HttpResponseSpec::$absoluteHeaders);
        $response->setHeader(HttpResponseSpec::META_HTTP_HEADER_CONTENT_TYPE, HttpResponseSpec::CONTENT_JSON);
        
        return $response;
    }
    
    public function _getRequestMethod (RunRequest $request)
    {
        return $request->getMeta(HttpRequestMetaSpec::REQUEST_METHOD, 'get');
    }
    
    public function process(RunRequest $request)
    {
        $request->meta[HttpRequestMetaSpec::EXECUTION_START] = microtime(1);
        
        $response = $this->_buildResponseObject($request);
        
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
            return $this->abnormalResponse(
                HttpResponseSpec::HTTP_CODE_ERROR,
                'Internal error : '. $throwable->getMessage().' on '.$throwable->getTraceAsString(),
                $response,
                $request
            );
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