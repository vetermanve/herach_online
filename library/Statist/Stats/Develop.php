<?php


namespace Statist\Stats;


use Statist\Config;

class Develop extends  AbstractStats {
    
    const PREFIX = 'runtime_duration_';
    
    const DAU_ERROR   = 'dau_error';
    
    public function getFields()
    {
        $r = [];
        
        foreach (range(0, 60, 20) as $time) {
            $r[] = self::PREFIX.$time;
        }
    
        return $r; 
    }
    
    public function getName()
    {
        return 'Скорость скриптов';
    }
    
    public function getId()
    {
        return Config::STATS_DEV;
    }
    
    public function getAreaStaking()
    {
        return 'percent';
    }
    
    public function getChartType()
    {
        return 'area';
    }
}