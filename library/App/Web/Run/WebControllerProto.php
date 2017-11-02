<?php


namespace App\Web\Run;


use Load\Load;
use Mu\Env;

abstract class WebControllerProto
{
    protected $template;
    
    public function render ($data, $template = null) 
    {
        $template = $template ?: $this->template;
        
        return Env::getRenderer()->render($template, $data);
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
}