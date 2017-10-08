<?php

namespace Statist;

use Statist\Digger\AbstractDigger;
use Statist\Loader\EmptyLoader;
use Statist\Processing\SharedCache;
use Statist\Stats\AbstractStats;

/**
 * Class Processor
 * 
 * Класс отвечающий за формирование биндов для сохранений статистики в таблицу
 * занимается агрегацией сырых евентов из очереди, 
 * поиском уников по этим эвентам и первичной агрегацией данных
 * 
 * @package Statist
 */
class Processor {

    const MAX_INT_32 = 4294967296;
    
    protected $companyId = 0;
    
    /**
     * Логические бинды уже разложенные по времени
     * 
     * @var array
     */
    private $binds     = [];
    
    /**
     * Связь ключей уников и бинд к которым они относятся
     * @var array
     */
    private $bindsUnq  = [];
    
    /**
     * Время жизни уников разного типа
     * 
     * @var array
     */
    private $unqTtl    = [];
    
    private $unqHashes    = [];
    
    private $unqHashesLife    = [];
    
    /**
     * @var AbstractStats[]
     */
    private $stats = [];
    
    /**
     * Айди пользователей от которых статистика разложенные
     * по филдам
     * 
     * @var
     */
    private $userIdsByFields;
    private $recordsByFields;
    
    /**
     * @var AbstractDigger[]
     */
    private $diggerModels = [];
    
    private $iterations = [];
    private $prevIterationTime;
    
    /**
     * @var SharedCache
     */
    protected $sharedCache;
    
    public function reportIteration($process, $data = null) 
    {
        $time = (string)microtime(1);
        $this->iterations[$time] = [
                $process, 
                ($this->prevIterationTime ? round(((float)$time - $this->prevIterationTime)*10000)/10  : 'start'), 
            ] 
            + ($data ? [2 => $data] : []);
        $this->prevIterationTime = $time;
        
        return $this->iterations[$time];
    }
    
    /* Обычный рантайм ведет себя в порядке следующих функций */
    
    
    public function prepareDiggers ()
    {
        $exFields = array_keys($this->recordsByFields);
        foreach ($this->stats as $stat) {
            // если в текущей пачке есть поля принадлежащие той или иной статистике
            if ($newFields = $stat->getExFieldsFilter($exFields)) {
                // получаем список группирощиков этой конкретной статистике
                foreach ($stat->getAllDiggers() as $digger) {
                    $digger->addFields($newFields);
                    $this->diggerModels[] = $digger;
                }
            }
        }
        
        $this->reportIteration('diggers found', ['digs' => count($this->diggerModels),]);
    }
    
    public function loadDiggersData ()
    {
        foreach ($this->diggerModels as $digger) {
            
            $userIds = [];
            foreach ($digger->getFieldsIdx() as $fdd => $_) {
                $userIds += $this->userIdsByFields[$fdd];
            }
            
            $digger->setIds(array_unique($userIds));
            $digger->setProcessor($this);
            $digger->digData();
            
            $this->reportIteration('digger ' . $digger->getClassName() . ' end load');
        }
    }
    
    public function processAllBinds ()
    {
        foreach ($this->diggerModels as $digger) {
            $this->processBinds($digger);
        }
    }
    
    public function processBinds (AbstractDigger $digger) 
    {
        $curTime = time();
        $binds = 0;
    
        $groupType = $digger->getId();
        $groupMap = $digger->getResult(); 
        $fieldsIdx = $digger->getFieldsIdx();
        
        foreach ($fieldsIdx as $field => $_) {
            $fieldCrc = $this->sharedCache->getFieldCrc($field);
            
            foreach ($this->recordsByFields[$field] as $rec) {
                $userId = $rec[Stats::ST_USER_ID];
                $count  = $rec[Stats::ST_COUNT];
    
                $time  = isset($rec[Stats::ST_TIME]) ? $rec[Stats::ST_TIME] : $curTime;
                $context  = isset($rec[Stats::ST_CONTEXT]) ? $rec[Stats::ST_CONTEXT] : [];
                $unqId = isset($context[Stats::DATA_UNQ]) ? $context[Stats::DATA_UNQ] : $userId;
    
                $unqId = ($unqId && (!is_numeric($unqId) || $unqId > self::MAX_INT_32)) 
                    ? $this->sharedCache->getFieldCrc($unqId) 
                    : $unqId;
    
                $timeMarks = $this->sharedCache->getDefaultTimeMarks($time);
    
                foreach ($timeMarks as $timeData) {
                    list($unqStoreHours, $timeMark) = $timeData;
        
                    $groupId = isset($groupMap[$userId]) ? $groupMap[$userId] : 0; // айди группы в нутри группировщика 
        
                    $bind = [
                        'company_id' => $this->companyId,
                        'field_id'   => $fieldCrc,
                        'group_type' => $groupType, // 32 bit айди группировщика
                        'group_id'   => $groupId, // айди группы в нутри группировщика
                        'time_id'    => $timeMark,
                    ];
        
                    $bindKey = implode('.', $bind);
        
                    if (!isset($this->binds[$bindKey])) {
                        $this->binds[$bindKey] = $bind + ['cnt' => $count, 'unq' => 0,];
                        $binds++;
                    } else {
                        $this->binds[$bindKey]['cnt'] += $count;
                    }
        
                    if ($unqId && $unqStoreHours) {
                        // all unq logic
                        $bindKeyUnq = $bindKey.'.'.$unqId;
                        $this->bindsUnq[$bindKeyUnq] = $bindKey;
                        $this->unqTtl[$bindKeyUnq] = $unqStoreHours;
            
                        // new unq logic
                        $this->unqHashes[$bindKey][$unqId] = $bindKeyUnq;
                        $this->unqHashesLife[$bindKey] = $unqStoreHours;
                    }
                }
            }
        }
    
        $this->reportIteration('digger ' . $digger->getClassName() . ' process binds', ['addedBinds' => $binds,]);
    }
    
    /**
     * @param $loaderId
     * @return \Statist\Loader\AbstractLoader
     */
    public function getLoader($loaderId) 
    {
        return $this->sharedCache->getLoader($loaderId);
    }
    
    
    /**
     * @return array
     */
    public function getIterations()
    {
        return $this->iterations;
    }
    
    /**
     * @return array
     */
    public function showIterations()
    {
        $res = '';
        foreach ($this->iterations as $it) {
            $res .= "\t".json_encode($it)."\n";
        }
        
        echo $res;
    }
    
    /**
     * @return array
     */
    public function getUnqHashes()
    {
        return $this->unqHashes;
    }
    
    /**
     * @return array
     */
    public function getBindsUnq()
    {
        return $this->bindsUnq;
    }
    
    /**
     * @return array
     */
    public function getBinds()
    {
        return $this->binds;
    }
    
    /**
     * @return array
     */
    public function getBindsCount()
    {
        return count($this->binds);
    }
    
    /**
     * @return array
     */
    public function getUnqHashesLife()
    {
        return $this->unqHashesLife;
    }
    
    /**
     * @param mixed $recordsByFields
     */
    public function setRecordsByFields($recordsByFields)
    {
        $this->recordsByFields = $recordsByFields;
    }
    
    /**
     * @param mixed $userIdsByFields
     */
    public function setUserIdsByFields($userIdsByFields)
    {
        $this->userIdsByFields = $userIdsByFields;
    }
    
    /**
     * @return int
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }
    
    /**
     * @param int $companyId
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }
    
    /**
     * @param mixed $sharedCache
     */
    public function setSharedCache($sharedCache)
    {
        $this->sharedCache = $sharedCache;
    }
    
    /**
     * @return Stats\AbstractStats[]
     */
    public function getStats()
    {
        return $this->stats;
    }
    
    /**
     * @param Stats\AbstractStats[] $stats
     */
    public function setStats($stats)
    {
        $this->stats = $stats;
    }
}
