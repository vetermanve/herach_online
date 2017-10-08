<?php


namespace Modular;


class ModularProcessEnvironment implements ModularSystemModule
{
    /**
     * @var ModularContainerProto
     */
    private $container;
    
    /**
     * @var ModularContextProto
     */
    private $context;
    
    /**
     * @return ModularContainerProto
     */
    public function getContainer()
    {
        return $this->container;
    }
    
    /**
     * @param ModularContainerProto $container
     *
     * @return $this
     */
    public function setContainer($container)
    {
        $this->container = $container;
        return $this;
    }
    
    /**
     * @return ModularContextProto
     */
    public function getContext()
    {
        return $this->context;
    }
    
    /**
     * @param ModularContextProto $context
     *
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;
        return $this;
    }
    
    /**
     * @param ModularSystemModule $module
     */
    public function makeFollower ($module) 
    {
        $module->setContext($this->context);
        $module->setContainer($this->container);
    }
}