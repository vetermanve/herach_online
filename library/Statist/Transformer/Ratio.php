<?php

namespace Statist\Transformer;

class Ratio extends AbstractTwoFlows {
    
    public function processBoot()
    {
        foreach ($this->data as $time => &$rec) {
            if (isset($this->secondFlow->data[$time])) {
//                $rec = round(($sourceData[$recordId]+($sourceData[$recordId]*$m))/$sourceData[$diffFRecordId]*10000-10000)/100;
                $rec = round($rec/$this->secondFlow->data[$time]*1000)/1000;
            } else {
                unset($this->data[$time]);
            }
        }
    }
    
}