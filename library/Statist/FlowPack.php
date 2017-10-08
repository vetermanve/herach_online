<?php

namespace Statist;

use Traversable;

/**
 * Class FlowPack
 * 
 * Класс коллекции столбцов данных статистики
 * 
 * @package Statist
 */
class FlowPack implements \Iterator {
    
    /**
     * @var FlowData[]
     */
    public $flows = [];
    
    /**
     * @var FlowData[][]
     */
    public $flowsByGroup = [];
    
    /**
     * @var FlowData[][]
     */
    public $flowsByField = [];
    
    
    /**
     * @var FlowData
     */
    public $sumFlow;
    /**
     * @var Dater
     */
    protected $dater;
    
    function __construct($dater)
    {
        $this->dater = $dater;
    }
    
    public function addFlow(FlowData $flow, $id = null)
    {
        
        if ($id) {
            $this->flows[$id] = $flow;    
        } else {
            $this->flows[] = $flow;
        }
        $flow->flowPack = $this;
        
        $this->flowsByGroup[$flow->group_id][$flow->fieldId] = $flow;
        $this->flowsByField[$flow->fieldId][$flow->group_id] = $flow;
    }
    
    public function removeFlow(FlowData $flow)
    {
        
    }
    
    public function bindFlow($data)
    {
//      'group_type' => string '0' (length=1)
//      'field_id' => string '3960890811' (length=10)
//      'time_id' => string '1423602001' (length=10)
//      'group_id' => string '0' (length=1)
//      'cnt' => string '5228553' (length=7)
//      'unq' => string '23412' (length=5)
            
        $dater = $this->dater;
        $fieldsOrder = $dater->getFieldsOrder();
        $fieldsIdx = array_flip($dater->getFields());
        $digger = $dater->getDigger();
        $hideSubtitle = $dater->getDigger()->isHideSubtitle();
        $names = $digger->getDataRange();
            
        $keys = array_flip(array_keys($names));
            
        foreach ($data as $row) {
            $fId = $row['field_id'] . '.' . $row['group_id'];
    
            if (!isset($this->flows[$fId])) {
                $flow = new FlowData();
                
                $flow->fieldId  = $row['field_id'];
                $flow->group_id = $row['group_id'];
                $flow->field    = $fieldsIdx[$flow->fieldId];
                $flow->title    = $fieldsIdx[$flow->fieldId];
                $flow->order    = ($fieldsOrder[$flow->fieldId]+1)*10000 + @$keys[$flow->group_id];
    
                if ($hideSubtitle) {
                    $flow->subTitle = '';
                } else {
                    $flow->subTitle = isset($names[$flow->group_id]) ? $names[$flow->group_id] : '#'.$flow->group_id;
                }
                
                $this->addFlow($flow, $fId);
            } else {
                $flow = $this->flows[$fId];
            }
        
            $flow->data[$row['time_id']]    = $row['cnt'];
            $flow->dataUnq[$row['time_id']] = $row['unq'];
        }
        
    }
    
    
    public function boot () 
    {
        usort($this->flows, function (FlowData $a,FlowData  $b) {
            if ($a->order !== null && $b->order !== null) {
                return $a->order > $b->order ? 1 : -1;
            }
            
            return strnatcmp($a->title, $b->title);
        });
    
        foreach ($this->flowsByGroup as $grId => &$flowList) {
            usort($flowList, function (FlowData $a,FlowData  $b) {
                if ($a->order !== null && $b->order !== null) {
                    return $a->order > $b->order ? 1 : -1;
                }
                
                return strnatcmp($a->title, $b->title);
            });
            
            $prev = null;
            foreach ($flowList as $flow) {
                if ($prev == null) {
                    $prev = $flow;
                    continue;
                }
    
                $prev->rightFlow = $flow;
                $prev = $flow;
            }
        }
    
        foreach ($this->flows as $flow) {
            $flow->boot();
        }
    
//        foreach ($this->flowsByGroup as $grId => $flows) {
//            $sumFlow = new FlowData();
//            $sumFlow->title = 'sum #'.$grId;
//            
//            foreach ($flows as $flow) {
//                foreach ($flow->data as $id => $count) {
//                    $sumFlow->data[$id] = isset($sumFlow->data[$id]) ? $sumFlow->data[$id] + $count : $count;
//                }
//            }
//            
//            $this->sumFlow[$grId] = $sumFlow;
//        }
    }
    
    public function minusRight () 
    {
    }
    
    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return current($this->flows);
    }
    
    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($this->flows);
    }
    
    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return key($this->flows);
    }
    
    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return is_object($this->current());
    }
    
    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset($this->flows);
    }
}
