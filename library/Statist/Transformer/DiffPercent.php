<?php


namespace Statist\Transformer;

class DiffPercent extends AbstractTwoFlows {
    
    public function processBoot()
    {
        foreach ($this->data as $time => &$rec) {
            if (isset($this->secondFlow->data[$time]) && $this->secondFlow->data[$time]) {
//                $rec = round(($sourceData[$recordId]+($sourceData[$recordId]*$m))/$sourceData[$diffFRecordId]*10000-10000)/100;
                $rec = (round($rec/$this->secondFlow->data[$time]*10000 - 10000)/100);
            } else {
                unset($this->data[$time]);    
            }
        }
    }
}