<?php


namespace App\Web\Run;


use App\Base\Run\BaseControllerProto;
use Load\Load;
use Mu\Env;

abstract class WebControllerProto extends BaseControllerProto
{
    protected $template = '';
    
    protected $templatePaths = [];
    
    
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
}