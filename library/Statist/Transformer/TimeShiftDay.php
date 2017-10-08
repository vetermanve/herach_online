<?php


namespace Statist\Transformer;


class TimeShiftDay extends AbstractOneFlow {
    
    public function processBoot()
    {
        $timeShift = 86400;
        $oldData = $this->data;
        $this->data = [];
        
        foreach ($oldData as $time => $rec) {
            $this->data[$time+$timeShift] = $rec;
        }
    }
}