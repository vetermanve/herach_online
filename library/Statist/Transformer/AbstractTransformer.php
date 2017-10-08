<?php


namespace Statist\Transformer;


use Statist\FlowData;
use Statist\Stats;

abstract class AbstractTransformer extends FlowData {
    
    /**
     * @var FlowData
     */
    protected $firstFlow;
    
    /**
     * @var FlowData
     */
    protected $secondFlow;
    
    
    abstract protected function processBoot();
    
    /**
     * @return FlowData
     */
    abstract protected function getPrimaryFlow();
    
    /**
     * @return FlowData[]
     */
    abstract protected function allFlows();
    
    final public function boot()
    {
        if ($this->booted) {
            return parent::boot();   
        }
        
        foreach ($this->allFlows() as $flow) {
            $flow->boot();
        }
        
        $this->bindIn($this->getPrimaryFlow());
        $this->processBoot();
        
        $this->booted = true;
        
        return parent::boot();
    }
    
}