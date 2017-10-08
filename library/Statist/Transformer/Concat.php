<?php


namespace Statist\Transformer;


class Concat extends AbstractTwoFlows {
    
    public function processBoot()
    {
        $this->title .=' concat';
        
        foreach ($this->data as $time => &$data) {
            $data .= isset($this->data[$time], $this->secondFlow->data[$time]) ? ' '.$this->secondFlow->data[$time] : ''; 
        }
    }
}