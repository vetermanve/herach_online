<?php

namespace Statist;

use Statist\Digger\AbstractDigger;
use Statist\Processing\SharedCache;
use Statist\Stats\AbstractStats;

/**
 * Class Dater
 * 
 * Класс знанимается агрегацией данных из табилцы статистики, 
 * чтобы вевести ее в админку или совершить над ней аналитику 
 * 
 * @package Statist
 */
class Dater {
    
    const SHIFT_ADD_FUTURE = 'future';
    const SHIFT_ADD_PAST = 'past';
    
    protected $fieldsOrder;
    private $fields;
    private $fromTime;
    private $toTime;
    private $filters;
    private $filter;
    private $oneField;
    private $aggregateBy = 'day';
    private $companyId;
    
    /**
     * @var AbstractStats
     */
    private $stats;
    
    /**
     * @var AbstractDigger
     */
    private $digger;
    
    private $currentView = 'all';
    private $currentViewData = array(
        'type' => 'all',
    );
    
    protected $viewData = [];
    protected $viewFields = [];
    protected $skipGraph = [];
    protected $parts = [];
    protected $logs = [];
    protected $curTpl = 'flow';
    protected $sharedCache;
    
    public function startPart($name) 
    {
        $this->parts[$name] = microtime(1);
    }
    
    public function stopPart ($name) 
    {
        $this->logs[] = $name.': '.(isset ($this->parts[$name]) ? round((microtime(1) - $this->parts[$name]) * 1000) : '-1').'мс'
        ;
    }
    
    /**
     * @var FlowPack
     */
    protected $flowPack;
    
    /**
     * @var FlowPack
     */
    protected $sourceFlow;
    
    function __construct()
    {
        $this->flowPack = new FlowPack($this);
        $this->sourceFlow = new FlowPack($this);
        
        $this->fromTime = strtotime('-7 day');
        $this->toTime = time();
        $this->sharedCache = new SharedCache();
    }
    
    /**
     * @return mixed
     */
    public function getFields()
    {
        return $this->fields;
    }
    
    public function getFieldsOrder()
    {
        return $this->fieldsOrder;
    }
    
    public function setStats(AbstractStats $stats) 
    {
        $this->stats = $stats;
    }
    
    /**
     * @param mixed $fields
     */
    public function setFields($fields)
    {
        $fields = (array) $fields;
        
        $resultFields = [];
        
        static $i = 0;
        
        foreach ($fields as $name => &$field) {
            if(!is_numeric($field)) {
                $resultFields[$field] = $this->sharedCache->getFieldCrc($field);
                $this->fieldsOrder[$resultFields[$field]] = $i++;
            } else{
                $resultFields[$name] = $field;
                $this->fieldsOrder[$field] = $i++;
            }
            
            
        }
        
        $this->fields = $resultFields;
    }
    
    public function getTimeInterval() 
    {
        return $this->aggregateBy === 'day' ?  86400 : 3600;
    }
    
    public function getLoadFilter () 
    {
        //DB index: company_id, group_type, time_id, field_id, group_id
        $filter = 
            [
                'company_id' => $this->companyId,
                'group_type' => $this->digger->getId(),
                'time_id' => $this->getRows(true),
                'field_id' => array_values($this->fields),
            ];
        
        if (is_numeric($this->groupInnerId)) {
            $filter['group_id'] = $this->groupInnerId;
        }
            
        return $filter;
    }
    
    public function setRawData ($rawData) 
    {
        $this->sourceFlow->bindFlow($rawData);   
    }
    
    public function processView ()
    {
        $this->startPart('Обработка данных');
        
        if ($this->currentViewData['type'] === 'resolutionOne') {
            $this->curTpl = 'resolution';
        }
        
        $this->buildViewData();
        
        $this->stopPart('Обработка данных');
    }
    
    private function _parseFields($fields) {
        if (is_array($fields)) {
            return $fields;
        } else if(is_callable($fields)) {
            return call_user_func($fields);
        }
        
        return [$fields];
    }
    
    private function getFieldsIds($fields) {
        $fieldsIds = [];
    
        foreach ($fields as $k => $field) {
            $fieldsIds[$k] = $this->sharedCache->getFieldCrc($field);
        }
        
        return $fieldsIds;
    }
    
    /**
     *
     */
    public function buildViewData()
    {
        $this->viewData = [];
        $this->defGrType = @$this->currentViewData['gr'];
        
        foreach ($this->currentViewData['fields'] as $id => $formatData) {
            $viewFormatter = $formatData['format'];
            $fieldsIds = $this->getFieldsIds($formatData['fields']);
            
            $firstFieldId = reset($fieldsIds);
            $objId = $id;
            
            $eachSingleFieldFormat = isset($formatData['applyAllFields']);
            
            $mainFieldsIds = $eachSingleFieldFormat ? $fieldsIds : [$firstFieldId];
            
            foreach ($mainFieldsIds as $fieldId) {
    
                $fieldsPassingToFormat = $eachSingleFieldFormat ? [$fieldId] : $fieldsIds;
                
                if (!isset($this->sourceFlow->flowsByField[$fieldId])) {
                    continue;
                }
                
                reset($this->sourceFlow->flowsByField[$fieldId]);
                $existedGroupers = array_keys($this->sourceFlow->flowsByField[$fieldId]); 
                
                foreach ($existedGroupers as $grouperId) {
                    $flowsPassingToFormat = [];
    
                    foreach ($fieldsPassingToFormat as $toFormatFieldId) {
                        if (!isset($this->sourceFlow->flowsByField[$toFormatFieldId][$grouperId])) {
                            break;
                        }
        
                        $flowsPassingToFormat[] = $this->sourceFlow->flowsByField[$toFormatFieldId][$grouperId];
                    }
    
                    if (count($flowsPassingToFormat) != count($fieldsPassingToFormat)) {
                        continue;
                    }
    
                    $resFlow = call_user_func_array($viewFormatter, $flowsPassingToFormat); // в этом месте вызывается космос
                    $resFlow->objId = $objId;
                    /* @var $resFlow FlowData */
                    $resFlow->bindInData($formatData);
                    $resFlow->copyFields(reset($flowsPassingToFormat), ['group_id','fieldId','field', 'order']);
    
                    $this->flowPack->addFlow($resFlow);
                }
            }
        }
        
        $this->flowPack->boot();
    }
    
    /**
     * @return mixed
     */
    public function getFilters()
    {
        return $this->filters;
    }
    
    /**
     * @param mixed $filters
     */
    public function setFilters($filters)
    {
        $this->filters = (array)$filters;
    }
    
    /**
     * @return mixed
     */
    public function getFilter()
    {
        return $this->filter;
    }
    
    /**
     * @param mixed $filter
     */
    public function setFilter($filter)
    {
        $this->filter = (array)$filter;
    }
    
    /**
     * @return AbstractStats
     */
    public function getStats()
    {
        return $this->stats;
    }
    
    public function getRows($shitType = null)
    {
        $toTime = $this->toTime;
        $fromTime = $this->fromTime;
        
        if ($this->aggregateBy == 'day') {
            $shitPoint = 86400;
            $shiftValue = ($shitType === null) 
                    ? 0 
                    : ($shitType === self::SHIFT_ADD_PAST ? $shitPoint : -$shitPoint);
            
            $fromTime = $fromTime - $shiftValue;
            
            $saveTimeZone = date_default_timezone_get();
            date_default_timezone_set('UTC');
            $start  = mktime(0, 0, 0, date('m', $fromTime), date('d', $fromTime), date('Y', $fromTime)) + 1;
            $toTime = mktime(0, 0, 0, date('m', $toTime), date('d', $toTime), date('Y', $toTime)) + 1;
            date_default_timezone_set($saveTimeZone);
        } else {
            $shitPoint = 3600;
            $shiftValue = ($shitType === null)
                ? 0
                : ($shitType === self::SHIFT_ADD_PAST ? $shitPoint : -$shitPoint);
    
            $fromTime = $fromTime - $shiftValue;
            
            $start  = floor(($fromTime) / 3600) * 3600;
            $toTime = ceil($toTime / 3600) * 3600;
        }
    
        return array_reverse(range($start, $toTime, $this->getTimeInterval()));
    }
    
    public function getRowsNamed()
    {
        $res = [];
        $mask = $this->aggregateBy !== 'day' ? 'd.m H:i' : 'd.m';
        foreach ($this->getRows(self::SHIFT_ADD_FUTURE) as $time) {
            $res[$time] = date($mask, $time);       
        }
        
        return $res;
    }
    
    public function setDigger($digger)
    {
        $this->digger = $digger;
    }
    
    public function getFieldsByParams()
    {
        $fields = [];
        foreach ($this->digger->getDataRange() as $grId => $digTitle) {
            foreach ($this->fields as $title => $fdd) {
                $fields[$title.($digTitle ? '-'.$digTitle : '')] = $fdd.'.'.$grId;
            }
        }
        
        return $fields;
    }
    
    /**
     * @return AbstractDigger
     */
    public function getDigger()
    {
        return $this->digger;
    }
    
    /**
     * @return string
     */
    public function getAggregateBy()
    {
        return $this->aggregateBy;
    }
    
    /**
     * @param string $aggregateBy
     */
    public function setAggregateBy($aggregateBy)
    {
        $this->aggregateBy = $aggregateBy;
    }
    
    /**
     * @return array
     */
    public function getViewFields()
    {
        return $this->viewFields;
    }
    
    /**
     * @return array
     */
    public function getViewData()
    {
        return $this->viewData;
    }
    
    protected $defGrType = 'line';
    
    public function getDefaultGraphType()
    {
        return $this->defGrType ? $this->defGrType  : $this->stats->getChartType();
    }
    
    /**
     * @return array
     */
    public function getSkipGraph()
    {
        return $this->skipGraph;
    }
    
    /**
     * @return string
     */
    public function getCurrentView()
    {
        return $this->currentView;
    }
    
    /**
     * @param string $viewId
     * 
     * @return $this
     */
    public function setCurrentView($viewId = null)
    {
        $views  = $this->stats->getViews();
        //print_r($views);
        $viewId = $viewId && isset($views[$viewId]) ? $viewId : key($views); 
        
        $this->currentView     = $viewId;
        $this->currentViewData = $views[$viewId] + $this->currentViewData;

        $fields = array();
        
        foreach ($this->currentViewData['fields'] as &$fieldInfo) {
            $fieldInfo['fields'] = $this->_parseFields($fieldInfo['fields']);
            $fields = array_merge($fields, $fieldInfo['fields']);
        }
        
        $this->setFields($fields);
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getCurrentViewData()
    {
        return $this->currentViewData;
    }
    
    /**
     * @return FlowPack
     */
    public function getFlowPack()
    {
        return $this->flowPack;
    }
    
    /**
     * @return array
     */
    public function getLogs()
    {
        return $this->logs;
    }
    
    protected $groupInnerId;
    protected $groupInnerName;
    
    /**
     * @return mixed
     */
    public function getGroupInnerId()
    {
        return $this->groupInnerId;
    }
    
    /**
     * @return mixed
     */
    public function isGroupInnerSet()
    {
        return is_numeric($this->groupInnerId);
    }
    
    /**
     * @param mixed $groupInnerId
     */
    public function setGroupInnerId($groupInnerId)
    {
        $this->groupInnerId = $groupInnerId;
        if($this->digger) {
            $range = $this->digger->getDataRange();
            $this->groupInnerName = isset($range[$this->groupInnerId]) ? $range[$this->groupInnerId] : 'что-то';
        }
    }
    
    /**
     * @return mixed
     */
    public function getGroupInnerName()
    {
        return $this->groupInnerName;
    }
    
    /**
     * @return FlowPack
     */
    public function getSourceFlow()
    {
        return $this->sourceFlow;
    }
    
    /**
     * @return mixed
     */
    public function getOneField()
    {
        return $this->oneField;
    }
    
    /**
     * @param mixed $oneField
     */
    public function setOneField($oneField)
    {
        $this->oneField = $oneField;
    }
    
    /**
     * @return string
     */
    public function getCurTpl()
    {
        return $this->curTpl;
    }
    
    /**
     * @return mixed
     */
    public function getFromTime()
    {
        return $this->fromTime;
    }
    
    /**
     * @param mixed $fromTime
     */
    public function setFromTime($fromTime)
    {
        $this->fromTime = $fromTime;
    }
    
    /**
     * @return mixed
     */
    public function getToTime()
    {
        return $this->toTime;
    }
    
    /**
     * @param mixed $toTime
     */
    public function setToTime($toTime)
    {
        $this->toTime = $toTime;
    }
    
    /**
     * @return mixed
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }
    
    /**
     * @param mixed $companyId
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
        $this->stats->setCompanyId($companyId);
    }
}
