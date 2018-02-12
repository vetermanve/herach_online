<?php


namespace App\Web\Run;


use App\Base\Run\BaseControllerProto;
use Load\Load;
use Mu\Env;

abstract class WebControllerProto extends BaseControllerProto
{
    public function run () 
    {
        return $this->{$this->method}();
    }
    
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
        $template = $template ?: $this->method;
    
        $templatesPaths[] = $this->_getTemplateDir();
        $templatesPaths[] = __DIR__.'/Template';
    
        $data['request_id'] = $this->requestOptions->getReqiestId();
        $data['env']['debug'] = (bool)$this->requestOptions->getParam('_debug');
        $data['static_host'] = Env::getEnvContext()->getScope('static','host', '');
        
        return Env::getRenderer()->render($template, $data, $templatesPaths);
    }
    
    /**
     * @param Load $loadRequest
     *
     * @return mixed
     */
    public function load (Load $loadRequest) 
    {
        return $this->getLoader()->addLoad($loadRequest)->processLoad();
    }
    
    /**
     * @return \Load\Executor\LoadExecutorProto
     */
    public function getLoader () 
    {
        return Env::getLoader();
    }
    
    protected function _getTemplateDir () 
    {
        $calledClassReflection = (new \ReflectionClass($this));
        $calledModule = dirname(dirname($calledClassReflection->getFileName()));
        $calledClassName = $calledClassReflection->getShortName();
        
        return $calledModule.'/Template/'.$calledClassName;
    }
    
    public function validateMethod ()
    {
        return method_exists($this, $this->method);
    }
}