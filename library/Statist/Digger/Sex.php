<?php

namespace Statist\Digger;

use Statist\Config;

class Sex extends AbstractDigger {
    
    public function digData()
    {
//        $users = $this->getProcessor()->getLoader(Config::LOAD_USERS)->getData($this->ids);
//        
//        foreach ($users as $id => $data) {
//            $this->result[$id] = (int)$data['sex'];
//        }
        
        $users = array_combine($this->ids, $this->ids);
        foreach ($users as $id => $data) {
            $this->result[$id] = ($id%2)+1;
        }
    }
    
    public function getId()
    {
        return Config::DIG_SEX;
    }
    
    public function getDataRange()
    {
        return array(
            1 => "Мужчины",
            2 => 'Женщины',
            0 => 'Инопланетяне',
        );
    }
    
    public function getName()
    {
        return 'Пол';
    }
}