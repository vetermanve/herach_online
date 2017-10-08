<?php


namespace Auth;


use Auth\Strategy\AuthStrategyProto;

class AuthProcessor extends AuthModuleProto
{
    /**
     * @var AuthStrategyProto[]
     */
    private $strategies = [];
    
    private $resultStrategy = [];
    
    public function addStrategy (AuthStrategyProto $strategy) 
    {
        $this->strategies[] = $strategy;
    }
    
    public function run () 
    {
        /**
         * @var AuthStrategyProto[] $active
         */
        $active = [];
        
        foreach ($this->strategies as $key => $strategy) {
            if ($strategy->shouldProcess()) {
                $active[] = $strategy;
            }
        }
    
        foreach ($active as $strategy) {
            $strategy->prepare();
        }
    
        $bestStrategy = [];
        $highestPriority = null;
        
        foreach ($active as $strategy) {
            $strategy->run();
            $authStatusPriority = $strategy->getAuthStatus();
            
            if ($authStatusPriority === AuthConfig::HIGHEST_PRIORITY) {
               $bestStrategy = $strategy;
               break;
            }
            
            if (!$highestPriority || AuthConfig::$authorizationPriority[$authStatusPriority] > $highestPriority) {
                $highestPriority = $authStatusPriority;
                $bestStrategy = $strategy;
            }
        }
        
        
    }
}