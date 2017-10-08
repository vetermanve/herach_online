<?php

namespace Statist\Digger;

use Statist\Config;
use Statist\Processing\SharedCache;

class Activity extends AbstractDigger {
    
    const ACT_NEWBIE         = 2;
    const ACT_START_ACTIVE   = 3;
    const ACT_START_NOACTIVE = 4;
    
    const ACT_LOST           = 1;
    const ACT_LOW            = 5;
    const ACT_NORM           = 6;
    const ACT_HIGH           = 7;
    
    public function digData()
    {
        $redis    = \RedisX::i();
        
        if (!$redis->isConnected()) {
            return false;
        }
    
        $users = $this->getProcessor()->getLoader(Config::LOAD_USERS)->getData($this->ids);
        
        $fieldCrc = (new SharedCache())->getFieldCrc('dau');
    
        $curTime     = time();
        $weekAgoTime = strtotime("-7 day");
        $start       = mktime(0, 0, 1, date('m', $weekAgoTime), date('d', $weekAgoTime), date('Y', $weekAgoTime));
        $timeMarks   = range($start, $start + 86400 * 6, 86400);
    
        $mainHashId = (0 << 80) | ($fieldCrc << 48) | (0 << 32);
        
        $userIds = array_combine(array_keys($users),array_keys($users));
        $heads = [];
        
        foreach ($timeMarks as $timeId) {
            $heads [$mainHashId | $timeId] = $userIds;
        }
        
        $headsIds = array_keys($heads);
        $unqData = $redis->multiHashMgetAssocKeys($heads);
        
        foreach ($users as $userId => $userData) { // суперактивные эт
            
            $sum = 0;
            foreach ($headsIds as $headId) {
                $sum += (int) $unqData[$headId][$userId];
            }
            
            $registeredTime = strtotime($userData['date_reg']);
            $daysRegistered = ceil(($curTime - $registeredTime) / 86400);
    
            if (!$sum && $daysRegistered > 1) {
                $this->result[$userId] = self::ACT_LOST;
                continue;
            }
    
            switch ($daysRegistered) {
                case 0:
                case 1:
                    $this->result[$userId] = self::ACT_NEWBIE;
                    break;
                case 2:
                    $this->result[$userId] = $sum >= 1 ? self::ACT_START_ACTIVE : self::ACT_START_NOACTIVE;
                    break;
                case 3:
                    $this->result[$userId] = $sum >= 2 ? self::ACT_START_ACTIVE : self::ACT_START_NOACTIVE;
                    break;
                case 4:
                case 5:
                    $this->result[$userId] = $sum > 2 ? ($sum == 3 ? self::ACT_NORM : self::ACT_HIGH ) : self::ACT_LOW;
                    break;
                case 6:
                case 7:
                    $this->result[$userId] = $sum > 2 ? ($sum <= 4 ? self::ACT_NORM : self::ACT_HIGH ) : self::ACT_LOW;
                    break;
                default:
                    $this->result[$userId] = $sum > 2 ? ($sum <= 4 ? self::ACT_NORM : self::ACT_HIGH ) : self::ACT_LOW;
                    break;
            };
        }
    }
    
    public function getId()
    {
        return Config::DIG_ACTIVITY;
    }
    
    public function getDataRange()
    {
        return array(
            self::ACT_NEWBIE => 'новички',
            self::ACT_START_ACTIVE => 'потенциально активные',
            self::ACT_START_NOACTIVE => 'потенциально неактивные',
            
            self::ACT_LOST => 'потеряные',
            self::ACT_LOW => 'малоактивные',
            self::ACT_NORM => 'среднеактивные',
            self::ACT_HIGH => 'суперактивные'
        );
    }
    
    public function getName()
    {
        return 'Активность';
    }
}