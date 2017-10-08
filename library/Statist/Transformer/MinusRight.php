<?php


namespace Statist\Transformer;

use Statist\FlowData;

class MinusRight extends AbstractOneFlow {
    
    public function processBoot()
    {
        if (!isset($this->rightFlow)) {
            return;
        }
        
        $this->rightFlow->boot();
        
        foreach ($this->data as $time => &$rec) {
            if (isset($this->rightFlow->data[$time]) && $this->rightFlow->data[$time]) {
                $rec = ($rec - $this->rightFlow->dataRelated[$time]);
            }
        }
    }
}