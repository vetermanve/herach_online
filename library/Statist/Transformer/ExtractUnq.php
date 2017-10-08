<?php


namespace Statist\Transformer;

class ExtractUnq extends AbstractOneFlow {
    
    protected function processBoot()
    {
        $this->data = $this->dataUnq;
    }
}