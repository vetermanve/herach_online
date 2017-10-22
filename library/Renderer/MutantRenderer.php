<?php

namespace Renderer;

use Renderer\TwigEngine;

class MutantRenderer
{
    
    function setData($data)
    {
        // TODO: Implement setData() method.
    }
    
    function setTemplate($template)
    {
        // TODO: Implement setTemplate() method.
    }
    
    public static function render($template, $data = [], $templatePath = '')
    {
        $engine = new TwigEngine();
        $engine->setData($data);
        $engine->setTemplatePath($templatePath);
        $engine->setTemplateName($template);
        
        return $engine->render();
    }
}