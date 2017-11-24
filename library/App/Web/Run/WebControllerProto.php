<?php


namespace App\Web\Run;


use Load\Load;
use Mu\Env;
use Mu\Interfaces\DispatcherInterface;

abstract class WebControllerProto
{
    protected $template = '';
    
    protected $templatePaths = [];
    
    /**
     * @var DispatcherInterface
     */
    protected $requestOptions;
    
    public function render ($data, $template = null) 
    {
        $template = $template ?: $this->template;
        $data['request_id'] = $this->requestOptions->getReqiestId();
        $data['env']['debug'] = (bool)$this->requestOptions->getParam('_debug');
        
        return Env::getRenderer()->render($template, $data, $this->templatePaths);
    }
    
    /**
     * @param mixed $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }
    
    public function load (Load $loadRequest) 
    {
        return Env::getLoader()->addLoad($loadRequest)->processLoad();
    }
    
    /**
     * @return array
     */
    public function getTemplatePaths(): array
    {
        return $this->templatePaths;
    }
    
    /**
     * @param array $templatePaths
     */
    public function setTemplatePaths(array $templatePaths)
    {
        $this->templatePaths = $templatePaths;
    }
    
    /**
     * @param DispatcherInterface $requestOptions
     */
    public function setRequestOptions(DispatcherInterface $requestOptions)
    {
        $this->requestOptions = $requestOptions;
    }
    
    public function p ($name, $default = null) 
    {
        return $this->requestOptions->getParam($name, $default);
    }
}