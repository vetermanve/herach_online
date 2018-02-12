<?php


namespace App\Base\Run;


use Mu\Interfaces\DispatcherInterface;

abstract class BaseControllerProto
{
    /**
     * @var DispatcherInterface
     */
    protected $requestOptions;
    
    /**
     * Requested method
     * 
     * @var string
     */
    protected $method = 'index';
    
    abstract public function run();
    
    abstract public function validateMethod();
    
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
    
    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }
    
    /**
     * @param string $method
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
    }
}