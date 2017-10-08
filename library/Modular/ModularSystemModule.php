<?php


namespace Modular;


interface ModularSystemModule
{
    /**
     * @param ModularContainerProto $container
     *
     * @return $this
     */
    public function setContainer ($container);
    
    /**
     * @return ModularContainerProto
     */
    public function getContainer ();
    
    /**
     * @param ModularContextProto $context
     *
     * @return $this
     */
    public function setContext ($context);
    
    /**
     * @return ModularContextProto
     */
    public function getContext ();
}