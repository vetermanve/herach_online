<?php


namespace Statist\Transformer;


use Statist\FlowData;

abstract class AbstractOneFlow extends AbstractTransformer {

    function __construct(FlowData $flow)
    {
        $this->firstFlow = $flow;
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
        return [$this->firstFlow];
    }
}