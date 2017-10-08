<?php


namespace Auth;


class AuthModuleProto
{
    /**
     * @var AuthContext
     */
    protected $context;
    
    /**
     * @return AuthContext
     */
    public function getContext()
    {
        return $this->context;
    }
    
    /**
     * @param AuthContext $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }
}