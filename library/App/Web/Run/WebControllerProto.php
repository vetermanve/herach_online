<?php


namespace App\Web\Run;


use App\Base\Run\BaseControllerProto;
use Load\Load;
use Mu\Env;

abstract class WebControllerProto extends BaseControllerProto
{
    protected $template = '';
    
    protected $templatePaths = [];
    
    public function _getCurrentUserId () 
    {
        $auth = new Load('auth-session');
        $auth->setParams([
            'id' => $this->getState('sid'),
        ]);
        $this->load($auth);
    
        $session = $auth->getResults();
        Env::getLogger()->info('Session ', $session);
        
        return isset($session['user_id']) ? $session['user_id'] : 0;
    }
    
    public function render ($data, $template = null) 
    {
        $template = $template ?: $this->template;
        $data['request_id'] = $this->requestOptions->getReqiestId();
        $data['env']['debug'] = (bool)$this->requestOptions->getParam('_debug');
        $data['static_host'] = Env::getEnvContext()->getScope('static','host', '');
        
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