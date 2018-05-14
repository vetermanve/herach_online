<?php

namespace App\Base\Run;

use Mu\Env;
use Renderer\TwigEngine;
use Run\ChannelMessage\ChannelMsg;
use Run\Rest\RestRequestOptions;
use Run\RunRequest;
use Run\Spec\HttpRequestMetaSpec;
use Run\Spec\HttpResponseSpec;
use Run\Util\HttpResourceHelper;
use Run\Util\SessionBuilder;

abstract class WebProcessorProto extends BaseRoutedProcessor
{
    private $spaceDir;
    
    public function prepare()
    {
        parent::prepare();
        
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
        $response = $this->_buildResponseObject($request);
        $response->setHeader(HttpResponseSpec::META_HTTP_HEADER_CONTENT_TYPE, HttpResponseSpec::CONTENT_HTML);
    
        // getting routing data
        $pathData = new HttpResourceHelper($request->getResource());
        $method = $pathData->getMethod() ?? 'index';
        
        if ($pathData->getId()) {
            $request->params['id'] = $pathData->getId();    
        }
        
        /* Try to execute */
        try {
            $controller = $this->_getControllerClass($request);
            
            if (!$controller || !($controller instanceof BaseControllerProto)) {
                return $this->abnormalResponse(
                    HttpResponseSpec::HTTP_CODE_NOT_FOUND,
                    'Resource not found',
                    $response,
                    $request
                );
            }
    
            $controller->setMethod($method);
            if (!$controller->validateMethod()) {
                return $this->abnormalResponse(
                    HttpResponseSpec::HTTP_CODE_NOT_FOUND,
                    'Incorrect method:'.$method,
                    $response,
                    $request
                );
            }
            
            // параметры запроса
            $options = new RestRequestOptions();
            $options->setRequest($request);
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