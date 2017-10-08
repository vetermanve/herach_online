<?php


namespace Statist\Transformer;


class ClearRelated extends AbstractOneFlow{
    
    public function processBoot()
    {
        $this->dataRelated = []; 
    }
}