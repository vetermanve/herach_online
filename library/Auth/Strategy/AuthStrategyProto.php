<?php


namespace Auth\Strategy;


use Auth\AuthModuleProto;
use Auth\DataProvider\SessionDataProviderProto;

abstract class AuthStrategyProto extends AuthModuleProto 
{
    /**
     * @var SessionDataProviderProto
     */
    protected $dataProvider;
    
    /**
     * @var integer
     */
    protected $authStatus;
    
    abstract public function shouldProcess();
    abstract public function prepare();
    abstract public function run();
    
    /**
     * @return SessionDataProviderProto
     */
    public function getDataProvider()
    {
        return $this->dataProvider;
    }
    
    /**
     * @param SessionDataProviderProto $dataProvider
     */
    public function setDataProvider($dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }
    
    /**
     * @return int
     */
    public function getAuthStatus()
    {
        return $this->authStatus;
    }
    
    /**
     * @param int $authStatus
     */
    public function setAuthStatus($authStatus)
    {
        $this->authStatus = $authStatus;
    }
}