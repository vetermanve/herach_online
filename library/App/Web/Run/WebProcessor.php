<?php

namespace App\Web\Run;

use Mu\Env;
use Renderer\TwigEngine;
use Run\ChannelMessage\ChannelMsgProto;
use Run\ChannelMessage\HttpReply;
use Run\Processor\RunRequestProcessorProto;
use Run\RunRequest;
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
    }
    
    public function process(RunRequest $request)
    {
        $request->getResource();
    
        $response = new HttpReply();
        $response->setUid($request->getUid());
        $response->setDestination($request->getReply());
        $response->setChannelState($request->getChannelState());
        $response->setHeaders(HttpResponseSpec::$absoluteHeaders);
        $response->setHeader('Content-Type', 'text/html; charset=UTF-8');
    
        $resParts = array_filter(explode('/', $request->getResource()));
        $module = isset($resParts[0]) ? ucfirst($resParts[0]): 'Landing';
        $controller = isset($resParts[1]) ? ucfirst($resParts[1]) : $module;
        $method = $resParts[2] ?? 'index';
        
        $controllerClass = '\\App\\Web\\'.$module.'\\Controller\\'.$controller;
        
        $templatesPaths[] = $this->spaceDir.'/'.$module.'/Template';
        $templatesPaths[] = $this->spaceDir.'/Run/Template';
        
        $template = $controller.'/'.$method;
        
        if (!class_exists($controllerClass)) {
            return $this->abnormalResponse(
                HttpResponseSpec::HTTP_CODE_NOT_FOUND,
                'Resource not found',
                $response,
                $request
            );
        }
    
        Env::getContainer()->setModule('renderer', function () use ($templatesPaths) {
            $renderer = new TwigEngine();
            $renderer->setTemplatePaths($templatesPaths);
            $renderer->init();
            
            return $renderer;
        });
        
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
    
            $controller->setTemplate($template);
            
            if (!method_exists($controller, $method)) {
                return $this->abnormalResponse(
                    HttpResponseSpec::HTTP_CODE_NOT_FOUND,
                    'Incorrect resource',
                    $response,
                    $request
                );
            }
            
            // Построим сессию
            $session = $this->sessionBuilder->getSession($request);
            $session->start();
    
            // Положим сессию в контейнер зависимостей, вдруг пригодится
            Env::getContainer()->setModule('session', $session);
            
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
        ///macdata/projects/mutants/reanima-back/library/App/Web/Run/Template
        
        $this->sendResponse($response, $request);
    }
    
    protected function abnormalResponse(int $code, string $text, ChannelMsgProto $response, RunRequest $request) {
        $response->setCode($code);
        $response->body = $text;
        $this->sendResponse($response, $request);   
    }
}