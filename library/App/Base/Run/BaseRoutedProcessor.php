<?php


namespace App\Base\Run;


use Run\ChannelMessage\HttpReply;
use Run\Processor\RunRequestProcessorProto;
use Run\RunRequest;
use Run\Spec\HttpResponseSpec;

abstract class BaseRoutedProcessor extends RunRequestProcessorProto
{
    private $appName = '';
    
    abstract public function getAppName();
    
    public function prepare()
    {
        $this->appName = $this->getAppName();
    }
    
    /**
     * @param RunRequest $request
     *
     * @return BaseControllerProto
     */
    protected function _getControllerClass(RunRequest $request) {
        $resParts = array_filter(explode('/', $request->getResource()));
        if (isset($resParts[0]) && $resParts[0]) {
            $moduleParts = explode('-', $resParts[0]);
            $moduleName = ucfirst(array_shift($moduleParts));
            if ($moduleParts) {
                array_walk($moduleParts, function (&$val) {
                    $val = ucfirst($val);
                });
                $controllerName = implode('', $moduleParts);
            } else {
                $controllerName = $moduleName;
            }
        } else {
            $moduleName = $controllerName = 'Landing';
        }
        
        $controllerClassName = $this->_getClassName($moduleName, $controllerName);
        
        if (!class_exists($controllerClassName)) {
            return null;
        }
        
        return new $controllerClassName;
    }
    
    protected function _getClassName($moduleName, $controllerName) {
        return '\\App\\'.$this->appName.'\\'.$moduleName.'\\Controller\\'.$controllerName;
    }
    
    public function _buildResponseObject (RunRequest $request)
    {
        $response = new HttpReply();
        $response->setUid($request->getUid());
        $response->setDestination($request->getReply());
        $response->setChannelState($request->getChannelState());
        $response->setHeaders(HttpResponseSpec::$absoluteHeaders);
        $response->setHeader(HttpResponseSpec::META_HTTP_HEADER_CONTENT_TYPE, HttpResponseSpec::CONTENT_TEXT);
        
        return $response;
    }
}