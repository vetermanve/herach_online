<?php

namespace Statist\Digger;

use Statist\Config;

class EmptyDigger extends AbstractDigger {
    
    public function digData()
    {
        
    }
    
    public function getId()
    {
        return Config::DIG_EMPTY;
    }
    
    public function getDataRange()
    {
        return array();
    }
    
    public function getName()
    {
        return 'Фигня какая-то';
    }
}