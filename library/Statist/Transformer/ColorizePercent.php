<?php


namespace Statist\Transformer;

class ColorizePercent extends AbstractOneFlow {
    
    public function processBoot()
    {
        foreach ($this->data as $time => &$rec) {
            if (!$rec) {
                $rec = '<span class="t-green-dark">0%</span>';
                continue;
            }
            
            if ($rec > 1000) {
                $rec = '';
                continue;
            }
            
            $rec = $rec > 0
                ? '<span class="t-green-dark">+'.$rec.'%</span>'
                : '<span class="t-red">'.$rec.'%</span>';
        }
    }
}