<?php


namespace Auth\Strategy;


use Auth\AuthConfig;
use Auth\DataProvider\MemoryDataProvider;

class NewSessionStrategy extends AuthStrategyProto
{
    public function shouldProcess()
    {
        return true;
    }
    
    public function prepare()
    {
        
    }
    
    public function run()
    {
        $this->authStatus = AuthConfig::AUTH_TOTAL_UNAUTHORIZED;
        $this->dataProvider = new MemoryDataProvider(); 
    }
}