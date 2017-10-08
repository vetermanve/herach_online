<?php


namespace Statist;


class View {
    
    protected $prototype;
    protected $field;
    
    function __construct($field, $prototype)
    {
        $this->field     = $field;
        $this->prototype = $prototype;
    }
    
    
}