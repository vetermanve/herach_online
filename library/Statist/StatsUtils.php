<?php


namespace Statist;
use Statist\Processing\SharedCache;

/**
 * Class StatsUtils
 * 
 * Класс для утилит, помогает вытаскивать внутренние данные статистики для того чтобы
 * использовать их не совсем по назначению
 * 
 * @package Statist
 */
class StatsUtils {
    
    public function checkUserUnqFieldsDay($userId, $days, $field) 
    {
        $redis = \RedisX::i();
        $sharedCache = new SharedCache();
        $fieldCrc = $sharedCache->getFieldCrc($field);
        $time = strtotime("-$days day");
        $start = mktime(0, 0, 1, date('m', $time), date('d', $time), date('Y', $time));
        $timeMarks = range($start, $start + 86400*$days, 86400);
        
        if ($redis->isConnected()) {
            
            $bindKeys = [];
            foreach ($timeMarks as $timeId) {
                $bind = [
                    'field_id' => $fieldCrc,
                    'group_type' => 0,
                    'group_id' => 0,
                    'time_id' => $timeId,
                    'user_id' => $userId,
                ];
        
                $bindKeys[$timeId] = implode('.', $bind);
            }
            
            return $redis->mgetAssocKeys($bindKeys);
        }
        
        return array_combine($timeMarks, array_fill(0, count($timeMarks), false));
    }
}