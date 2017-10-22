<?php


namespace App\Web\Run;


use Mu\Env;
use Renderer\MutantRenderer;

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
}