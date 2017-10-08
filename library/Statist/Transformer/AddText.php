<?php


namespace Statist\Transformer;

use Statist\FlowData;

class AddText extends AbstractOneFlow {
    
    protected $prefix = '';
    protected $postfix = '';
    
    function __construct(FlowData $flow, $prefix = '', $postfix = '')
    {
        $this->postfix = $postfix;
        $this->prefix = $prefix;
        
        parent::__construct($flow);
    }
    
    
    public function processBoot()
    {
        foreach ($this->data as $time => &$rec) {
            if ($rec === '') {
                continue;
            }
            
            $rec = $this->prefix.$rec.$this->postfix;
        }
    }
}