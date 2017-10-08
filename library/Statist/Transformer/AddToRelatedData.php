<?php


namespace Statist\Transformer;


class AddToRelatedData extends AbstractTwoFlows {

    public function processBoot()
    {
        $this->dataRelated = $this->secondFlow->data;
    }
}