<?php


namespace App\Rest\Run;


use Run\ChannelMessage\ChannelMsg;
use Run\ChannelMessage\HttpReply;
use Run\Processor\RunRequestProcessorProto;
use Run\Rest\RestRequestOptions;
use Run\RunRequest;
use Run\Spec\HttpRequestMetaSpec;
use Run\Spec\HttpResponseSpec;

class RestInternalProcessor extends RunRequestProcessorProto
{
    
    public function prepare()
    {
        // TODO: Implement prepare() method.
    }
    
    public function process(RunRequest $request)
    {
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
        
        $controllerClass = '\\App\\Rest\\'.$moduleName.'\\Controller\\'.$controllerName;
    
        $response = new HttpReply();
        $response->setUid($request->getUid());
        $response->setDestination($request->getReply());
        $response->setChannelState($request->getChannelState());
        $response->setHeaders(HttpResponseSpec::$absoluteHeaders);
        $response->setHeader('Content-Type', 'application/json; charset=UTF-8');
    
        if (!class_exists($controllerClass)) {
            return $this->abnormalResponse(
                HttpResponseSpec::HTTP_CODE_NOT_FOUND,
                'Resource not found',
                $response,
                $request
            );
        }
    
        $method = $request->getMeta(HttpRequestMetaSpec::REQUEST_METHOD, 'get');
    
        try {
            $controller = new $controllerClass;
        
            if (!$controller instanceof RestControllerProto) {
                return $this->abnormalResponse(
                    HttpResponseSpec::HTTP_CODE_NOT_FOUND,
                    'Unsupported method',
                    $response,
                    $request
                );
            }
        
            if (!method_exists($controller, $method)) {
                return $this->abnormalResponse(
                    HttpResponseSpec::HTTP_CODE_NOT_FOUND,
                    'Incorrect resource',
                    $response,
                    $request
                );
            }
            
            $options = new RestRequestOptions();
            $options->setRequest($request);
            $controller->setRequest($options);
        
            $response->setCode(HttpResponseSpec::HTTP_CODE_OK);
            $response->setBody($controller->{$method}());
        
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
}