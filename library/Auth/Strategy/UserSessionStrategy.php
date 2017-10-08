<?php


namespace Auth\Strategy;


use Auth\AuthConfig;
use Auth\AuthContext;
use Auth\DataProvider\RedisSessionDataProvider;

class UserSessionStrategy extends AuthStrategyProto
{
    public function shouldProcess()
    {
        return $this->context->is(AuthContext::SESSION_ID);
    }
    
    public function prepare()
    {
        $this->dataProvider = new RedisSessionDataProvider(); 
    }
    
    public function run () 
    {
        if ($this->dataProvider->get(AuthConfig::FIELD_USER_ID)) {
            $this->authStatus = AuthConfig::AUTH_USER_AUTHORIZED;
        } else {
            $this->authStatus = AuthConfig::AUTH_USER_UNAUTHORIZED;
        }
    }
    
    public function getAuthType()
    {
        return $this->authStatus;
    }
    
    public function isAuthenticated()
    {
        $id = $this->context->get(AuthContext::SESSION_ID);
        $this->dataProvider->setToken($id);
    }
}