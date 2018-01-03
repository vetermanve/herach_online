<?php

namespace Renderer;

use Twig_Environment;
use Twig_Loader_Filesystem;

class TwigEngine implements MuRenderer
{
    /**
     * @var Twig_Environment[]
     */
    private $environments = [];
    
    public function render(string $templateName, array $data = [], $templatePaths = [])
    {
        $envId = crc32(json_encode($templatePaths));
        $debug = true;
        
        try {
            if (!isset($this->environments[$envId])) {
                $loader = new Twig_Loader_Filesystem($templatePaths);
                $env =  new Twig_Environment($loader);
                $this->environments[$envId] = $env;
            } else {
                $env = $this->environments[$envId];
            }
            
            $page = $env->render($templateName.'.twig', $data);    
        } catch (\Twig_Error $error) {
            $page = 'Page error: '.($debug ? $error->getMessage() : '');
        }
    
        return $page; 
    }
}