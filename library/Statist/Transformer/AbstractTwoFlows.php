<?php


namespace Statist\Transformer;


use Statist\FlowData;

abstract class AbstractTwoFlows extends AbstractTransformer {
    
    /**
     *
     * @param $firstFlow FlowData
     * @param $secondFlow FlowData
     */
    public function __construct($firstFlow, $secondFlow)
    {
        $this->firstFlow  = $firstFlow;
        $this->secondFlow = $secondFlow;
    }
    
    /**
     * @return FlowData
     */
    protected function getPrimaryFlow()
    {
        return $this->firstFlow;
    }
    
    /**
     * @return FlowData[]
     */
    protected function allFlows()
    {
        return [$this->firstFlow, $this->secondFlow];
    }
}