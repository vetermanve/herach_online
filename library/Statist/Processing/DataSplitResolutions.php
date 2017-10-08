<?php


namespace Statist\Processing;


use Statist\Config;
use Statist\Stats;

class DataSplitResolutions extends DataUserProto
{
    protected $resolutions;
    
    public function loadResolutions() {
        
        // подготовим запросы на резолюции
        $stats = Config::getAvailableStats();
        $this->resolutions = [];
    
        $addData = [];
        
        foreach ($stats as $stat) {
            $resConfigs = $stat->getResolutionConfig();
            
            if (!$resConfigs) {
                continue;
            }
            
            foreach ($resConfigs as $resolutionId => $config) {
                list($fieldName, $range) = $config;
                
                if(!isset($this->resolutions[$fieldName])) {
                    $this->resolutions[$fieldName] = $range;
                } else {
                    $this->resolutions[$fieldName] = array_unique(array_merge($this->resolutions[$fieldName], $range));
                }
            }
        }
        
        foreach ($this->resolutions as &$range) {
            sort($range);
        }
        
        // посчитаем кол-во хитов на пользователя по полю для тех полей к которым есть резолюции
        $resolutionsData = [];
        $timeMark = (int)mktime(0, 0, 1,  date('m'), date('d'), date('Y')); // this day start + 1 second
        $keyPrefix = 'resolution';
        $expireAt = $timeMark + 25 * 3600;
        $resolutionFieldsCount = 0;
        
        foreach ($this->dataContainer->data as $rec) {
            if (isset($this->resolutions[$rec[Stats::ST_FIELD]])) {
                
                $userId = $rec[Stats::ST_USER_ID];
                $field  = $rec[Stats::ST_FIELD];
                $count  = (int)$rec[Stats::ST_COUNT];
                
                $mainKey = $keyPrefix.'-'.$field.':'.$timeMark;
                
                if (isset($resolutionsData[$mainKey][$userId])) {
                    $resolutionsData[$mainKey][$userId][0] += $count;
                } else {
                    $resolutionsData[$mainKey][$userId] = [$count, $field];
                }
                
                $resolutionFieldsCount++;
            }
        }
        
//        $this->reportIteration('resolutions constructed', ['found' => $resolutionFieldsCount,]);
        if (!$resolutionsData ) {
            return;
        }
        
        die('please fix redis instance');  
        $redis = \RedisX::i(); 
        $expirationWasSet = [];
        $resolutionBindsCount = 0;
        
        if ($redis->isConnected()) {
            $storedResolutions = $redis->multiHashMgetAssocKeys($resolutionsData);
//            $this->reportIteration('resolutions loaded', ['countHeads' => count($resolutionsData),]);
            
            foreach ($resolutionsData as $mainKey => $usersInfo) {
                foreach ($usersInfo as $userId => $countAndField) {
                    list($packetCount, $fieldName) = $countAndField;
                    
                    $hitStart = (int)$storedResolutions[$mainKey][$userId];
                    $hitEnd = $hitStart + $packetCount;
                    
                    foreach ($this->resolutions[$fieldName] as $hitMark) { /// ex. [10,20,30,40,50,60]
                        if ($hitMark <= $hitStart) { // ex. 1
                            continue;
                        }
                        
                        if ($hitMark > $hitEnd) { // ex. 61
                            break;
                        }
                        
                        $addData[] = [ // add resolution packet 
                                          Stats::ST_FIELD   => $fieldName.'_'.$hitMark,
                                          Stats::ST_USER_ID => $userId,
                                          Stats::ST_COUNT   => 1,
                                          Stats::ST_CONTEXT => array(
                                              Stats::DATA_CUSTOM_TIME_MARK => array(
                                                  SharedCache::TIME_MARK_DAY =>[
                                                      0 => 0, //  unq store hours
                                                      1 => $timeMark // time mark
                                                  ]
                                              ),
                                          ),
                        ];
                        
                        $resolutionBindsCount++;
                    }
                    
                    $redis->hIncrBy($mainKey, $userId, $packetCount);
                    
                    if (!isset($expirationWasSet[$mainKey])) { // новый ключ, надо выставить ему экспайр
                        $redis->expireAt($mainKey, $expireAt);
                        $expirationWasSet[$mainKey] = 1;
                    }
                }
            }
        } else {
//            $this->reportIteration('resolutions not loaded: Redis disconnected');
        }
        
        $this->dataContainer->addData($addData);
//        $this->reportIteration('resolutions inserted', ['bindsAdded' => $resolutionBindsCount]);
    }
}