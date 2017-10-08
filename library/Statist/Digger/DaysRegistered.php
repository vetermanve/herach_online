<?php

namespace Statist\Digger;

use Statist\Config;

class DaysRegistered extends AbstractDigger {
    
    const TIME_1_DAY   = 1;
    const TIME_7_DAY   = 2;
    const TIME_14_DAY  = 3;
    const TIME_30_DAY  = 4;
    const TIME_MORE_30 = 5;
    
    public function digData()
    {
        $users = $this->getProcessor()->getLoader(Config::LOAD_USERS)->getData($this->ids);
        
        $marks = array(
            self::TIME_1_DAY => strtotime('-1 day'),
            self::TIME_7_DAY => strtotime('-7 day'),
            self::TIME_14_DAY => strtotime('-14 day'),
            self::TIME_30_DAY => strtotime('-30 day'),
            self::TIME_MORE_30 => 0
        );
        
        foreach ($users as $id => $data) {
            $registered = strtotime($data['date_reg']);
            
            foreach ($marks as $rangeId => $time) {
                if ($registered >= $time) {
                    $this->result[$id] = $rangeId;
                    break;
                }
            }
        }
    }
    
    public function getId()
    {
        return Config::DIG_DAYS_REGISTRED;
    }
    
    public function getDataRange()
    {
        return array(
            self::TIME_7_DAY => '1 день',
            self::TIME_1_DAY => '2-7 дней',
            self::TIME_14_DAY => '7-14 дней',
            self::TIME_30_DAY => '14-30 дней',
            self::TIME_MORE_30 => 'более 30 дней'
        );
    }
    
    public function getName()
    {
        return 'Дней в приложении';
    }
}