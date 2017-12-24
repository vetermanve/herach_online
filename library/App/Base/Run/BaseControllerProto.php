<?php


namespace App\Base\Run;


use Mu\Interfaces\DispatcherInterface;

abstract class BaseControllerProto
{
    /**
     * @var DispatcherInterface
     */
    protected $requestOptions;
    
    final public function p($name, $default = null)
    {
        return $this->requestOptions->getParam($name, $default);
    }
    
    public function getState ($name, $default = null)
    {
        return $this->requestOptions->getState($name, $default);
    }
    
    public function setState ($name, $value, $ttl = null)
    {
        $this->requestOptions->setState($name, $value, $ttl);
    }
    
    /**
     * @param DispatcherInterface $requestOptions
     */
    public function setRequestOptions(DispatcherInterface $requestOptions)
    {
        $this->requestOptions = $requestOptions;
    }
}