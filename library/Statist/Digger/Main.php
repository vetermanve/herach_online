<?php

namespace Statist\Digger;

use Statist\Config;

class Main extends AbstractDigger {
    
    public function digData()
    {
        foreach ($this->ids as $id) {
            $this->result[$id] = 0;
        }
    }
    
    public function getId()
    {
        return Config::DIG_MAIN;
    }
    
    public function getDataRange()
    {
        return array(
            0 => 'Все',
        );
    }
    
    public function getName()
    {
        return 'Все';
    }
    
    public function isShowName () 
    {
        return false;
    }
    
    public function isHideSubtitle()
    {
        return true;
    }
}