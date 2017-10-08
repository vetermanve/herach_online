<?php

namespace Statist;

use Statist\Digger\AbstractDigger;
use Statist\Digger\EmptyDigger;
use Statist\Stats\AbstractStats;

/**
 * Class Config
 * 
 * Основные конфиги и константы статистики, а так же способы констракта моделей по константам
 * @package Statist
 */
class Config {
    
    const DIG_MAIN           = 0;
    const DIG_TRAFFIC_SOURCE = 1;
    const DIG_SEX            = 2;
    const DIG_EMPTY          = 3;
    const DIG_DAYS_REGISTRED = 4;
    const DIG_ACTIVITY       = 5;
    
    const DIG_OPERATOR       = 6;
    
    const STATS_MAIN        = 'main';
    const STATS_MONEY       = 'money';
    const STATS_TRAFFIC     = 'traff';
    const STATS_DEV         = 'dev';
    const STATS_DAILY_BONUS = 'dailyBonus';
    const STATS_CHATS = 'chats';
    const STATS_EVOTOR = 'evotor';
    
    const LOAD_USERS = 'Users';
    
    protected static $stats;
    
    /**
     * @param int $comapnyId
     *
     * @return Stats\AbstractStats[]
     */
    public static function getAvailableStats()
    {
        if(!self::$stats) {
            self::$stats = self::getAllStats();
        }
        
        return self::$stats;
    }
    
    public static function getAllStats($companyId = 0)
    {
        /* @var $stats AbstractStats[] */
        $stats =  array(
            self::STATS_MAIN => new Stats\Main(),
            self::STATS_CHATS => new Stats\Chats(),
            self::STATS_EVOTOR => new Stats\Evotor()
        );
        
        if ($companyId) {
            foreach ($stats as $stat) {
                $stat->setCompanyId($companyId);
            }
        }
        
        return $stats;
    }
    
    public static function getAllAvailableFields()
    {
        
    }
    
    /**
     * @return AbstractDigger
     */
    public static function getDigger($diggerId)
    {
        $models = array(
            self::DIG_MAIN => 'Main',
            self::DIG_SEX => 'Sex',
//            self::DIG_TRAFFIC_SOURCE => 'Operator',
//            self::DIG_DAYS_REGISTRED => 'DaysRegistered',
//            self::DIG_ACTIVITY => 'Activity',
        );
        
        $modelName = isset($models[$diggerId]) && class_exists('Statist\\Digger\\'.$models[$diggerId]) ? 'Statist\\Digger\\'.$models[$diggerId] : false;
        return $modelName ? new $modelName() : new EmptyDigger();
    }
}
