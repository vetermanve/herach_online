<?php

namespace Renderer;

use Twig_Environment;
use Twig_Loader_Filesystem;

class TwigEngine implements MuRenderer
{
    protected $templatePaths;
    
    /**
     * @var Twig_Environment
     */
    private $renderer;
    
    
    public function init () 
    {
        $loader = new Twig_Loader_Filesystem($this->templatePaths);
        $this->renderer = new Twig_Environment($loader);
    }
    
    /**
     * @return mixed
     */
    public function getTemplatePaths()
    {
        return $this->templatePaths;
    }
    
    /**
     * @param mixed $templatePaths
     */
    public function setTemplatePaths($templatePaths)
    {
        $this->templatePaths = $templatePaths;
    }
    
    public function render(string $templateName, array $data = [])
    {
        return $this->renderer->render($templateName.'.twig', $data);
    }
}