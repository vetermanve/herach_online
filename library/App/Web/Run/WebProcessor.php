<?php

namespace App\Web\Run;

use Mu\Env;
use Renderer\TwigEngine;
use Run\ChannelMessage\ChannelMsg;
use Run\ChannelMessage\HttpReply;
use Run\Processor\RunRequestProcessorProto;
use Run\Rest\RestRequestOptions;
use Run\RunContext;
use Run\RunRequest;
use Run\Spec\HttpRequestMetaSpec;
use Run\Spec\HttpResponseSpec;
use Run\Util\SessionBuilder;

class WebProcessor extends RunRequestProcessorProto
{
    private $spaceDir;
    
    public function prepare()
    {
        if (!$this->sessionBuilder) {
            $this->sessionBuilder = new SessionBuilder();
        }
    
        $this->sessionBuilder->follow($this);
        
        $this->spaceDir = dirname(__DIR__);
    
        Env::getContainer()->setModule('renderer', function () {
            return new TwigEngine();
        });
    }
    
    public function process(RunRequest $request)
    {
        if ($this->context->getEnv(RunContext::ENV_DEBUG)) {
            $request->params['_debug'] = true;
        }
        
        $response = new HttpReply();
        $response->setUid($request->getUid());
        $response->setDestination($request->getReply());
        $response->setChannelState($request->getChannelState());
        $response->setHeaders(HttpResponseSpec::$absoluteHeaders);
        $response->setHeader(HttpResponseSpec::META_HTTP_HEADER_CONTENT_TYPE, HttpResponseSpec::CONTENT_HTML);
    
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
        
        $method = $request->getMeta(HttpRequestMetaSpec::REQUEST_METHOD) ?? 'index';
        
        $controllerClass = '\\App\\Web\\'.$moduleName.'\\Controller\\'.$controllerName;
        
        if (!class_exists($controllerClass)) {
            return $this->abnormalResponse(
                HttpResponseSpec::HTTP_CODE_NOT_FOUND,
                'Resource not found',
                $response,
                $request
            );
        }
        
        /* Try to execute */
        try {
            $controller = new $controllerClass;
            
            if (!$controller instanceof WebControllerProto) {
                return $this->abnormalResponse(
                    HttpResponseSpec::HTTP_CODE_NOT_FOUND,
                    'Incorrect resource',
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
    
    
            // все что нужно шаблонизатору
            
            // параметры запроса
            $options = new RestRequestOptions();
            $options->setRequest($request);
            $controller->setMethod($method);
            $controller->setRequestOptions($options);
    
            // Построим сессию
            $session = $this->sessionBuilder->getSession($request);
            $session->start();
    
            // Положим сессию в контейнер зависимостей, вдруг пригодится
            Env::getContainer()->setModule('session', $session);
            
            $response->setCode(HttpResponseSpec::HTTP_CODE_OK);
            $response->setBody($controller->run());
            
        } catch (\Throwable $throwable) {
            return $this->abnormalResponse(
                HttpResponseSpec::HTTP_CODE_ERROR,
                'Internal error ('.get_class($throwable).') : '. $throwable->getMessage().' on '.$throwable->getTraceAsString(),
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