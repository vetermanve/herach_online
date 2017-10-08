<?php

namespace Statist\Stats;

use Statist\Config;
use Statist\Digger\AbstractDigger;
use Statist\FlowData;
use Statist\Graph;
use Statist\Transformer\AddText;
use Statist\Transformer\AddToRelatedData;
use Statist\Transformer\ColorizePercent;
use Statist\Transformer\Concat;
use Statist\Transformer\Diff;
use Statist\Transformer\DiffPercent;
use Statist\Transformer\ExtractUnq;
use Statist\Transformer\Ratio;
use Statist\Transformer\RatioPercent;
use Statist\Transformer\TimeShiftDay;
use Statist\View;

abstract class AbstractStats {
    
    protected $globalDiggers = null;
    protected $specificDiggers = null;
    
    protected $companyId = 0;
    protected $exFields;
    
    protected static $defaultDiggers = [
        Config::DIG_MAIN
    ];
    
    protected static $fields = [];
    
    abstract public function getName();
    abstract public function getId();
    
    public function getFields()
    {
        $class = get_called_class();
        
        if (!isset(self::$fields[$class])) {
            self::$fields[$class] = [];
            $const = (new \ReflectionClass($this))->getConstants();
            
            foreach ($const as $name => $value) {
                if (strpos($name, 'F_') === 0) {
                    self::$fields[$class][] = $value;
                }
            }
        }
        
        return self::$fields[$class];
    }
    
    public function getViews()
    {
        return [];
    }
    
    public function hasUnique() 
    {
        return false;
    }
    
    
    
    public function getExFieldsFilter($fCheck) 
    {
        $this->getExFields();
        return array_intersect($this->exFields, $fCheck);
    }
    
    /**
     * @return mixed
     */
    public function getExFields()
    {
        if (!$this->exFields) {
            $this->exFields = array_merge($this->getFields(), $this->getResolutionFields());
        }
        
        return $this->exFields;
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
    
    final public function getGlobalDiggers() 
    {
        if ($this->globalDiggers === null) {
            $this->globalDiggers = array_merge(self::$defaultDiggers, $this->loadGlobalDiggers());
        }
        
        return $this->globalDiggers;
    }
    
    protected function loadGlobalDiggers () 
    {
        return [];
    }
    
    /**
     * @return AbstractDigger[]
     */
    public function getSpecificDiggers()
    {
        if($this->specificDiggers === null) {
            $this->specificDiggers = [];
            
            $diggers = $this->loadSpecificDiggers();
            
            foreach ($diggers as $digger) {
                $this->specificDiggers[$digger->getId()] = $digger->setCompanyId($this->companyId);
            }
        }
        
        return $this->specificDiggers;
    }
    
    /**
     * @return AbstractDigger[]
     */
    public function loadSpecificDiggers ()
    {
        return [];
    }
    
    public function getAreaStaking()
    {
        return 'normal';
    }
    
    public function getChartType()
    {
        return Graph::GR_LINE;
    }
    
    public function getDefaultView () 
    {
        return $this->calcDayDiffAbsolute();
    }
    
    /**
     * Достать из потока УНИКИ и доавить к ним разницу От Предыдущего дня
     * 
     * @return callable
     */
    public function calcDayDiffUnique() 
    {
        return function (FlowData $flow) {
            $flow        = new ExtractUnq($flow);
            return new AddToRelatedData($flow, new ColorizePercent(new DiffPercent($flow, new TimeShiftDay($flow))));
        };
    }
    
    /**
     * Вычислить отношение в процентах Для УНиков, добваить разницу от пред. дня для этих процентов
     * 
     * @return callable
     */
    public function calсUniquePercentRatioOfTowValuesWithDayDiff()
    {
        return function (FlowData $flowBr, FlowData $flowDau) {
            $flow = new RatioPercent(new ExtractUnq($flowBr), new ExtractUnq($flowDau));
        
            return new AddToRelatedData($flow, new ColorizePercent(new Diff($flow, new TimeShiftDay($flow))));
        };
    }
    
    /**
     * Вычислить отношение в процентах Для УНиков, добваить разницу от пред. дня для этих процентов
     * и дорисовать процентики к значению
     * 
     * @return callable
     */
    public function calсUniquePercentRatioOfTowValuesWithDayDiffAddPercents()
    {
        return function (FlowData $flowBr, FlowData $flowDau) {
            $flow = new RatioPercent(new ExtractUnq($flowBr), new ExtractUnq($flowDau));
    
            return new AddToRelatedData(new AddText($flow, '', '<i>%</i>'), new ColorizePercent(new Diff($flow, new TimeShiftDay($flow))));
        };
    }
    
    /**
     * Вычислить отношение Поля со Своими Униками (кол-во на уника) и добавить РазницуВПроцентах от предыдущего дня
     * 
     * @return callable
     */
    public function calcRatioWithSelfUniqueWithDayDiff () 
    {
        return function (FlowData $flowBr) {
            $flow = new Ratio($flowBr, new ExtractUnq($flowBr));
            
            return new AddToRelatedData($flow, new ColorizePercent(new DiffPercent($flow, new TimeShiftDay($flow))));
        };
    }
    
    
    /**
     * Вычислить отношение Двух полей по абсолютному значению с разницей.
     * 
     * @return callable
     */
    public function calcAbsoluteDiffOfTwoValuesWithDayDiff ()
    {
        return function (FlowData $flowBr, FlowData $flowDau) {
            $flow = new RatioPercent($flowBr, $flowDau);
            
            return new AddToRelatedData($flow, new ColorizePercent(new Diff($flow, new TimeShiftDay($flow))));
        };
    }
    
    /**
     * Добавить разницу поля по абсолютному значению с предыдущим днем 
     * 
     * @return callable
     */
    public function calcDayDiffAbsolute () 
    {
        return function (FlowData $flow) {
            return new AddToRelatedData($flow, new ColorizePercent(new DiffPercent($flow, new TimeShiftDay($flow))));
        };
    }
    
    public function getResolutionConfig () 
    {
        return [];
    }
    
    public function getResolutionFields()
    {
        $fields = array();
        
        foreach ($this->getResolutionConfig() as $oneConfig) {
            list ($field, $range) = $oneConfig;
            
            foreach (array_unique($range) as $mark) {
                $fields[] = $field.'_'.$mark;
            }
        }
        
        return $fields;
    }
    
    public function getResolutionFieldsById($id)
    {
        $fields = array();
        $config = $this->getResolutionConfig();
        
        if(!isset($config[$id])) {
            return $fields;
        }
        
        list($field, $range) = $config[$id];
        
        foreach (array_unique($range) as $mark) {
            $fields[] = $field.'_'.$mark;
        }
        
        return $fields;
    }
    
    /**
     * @return AbstractDigger[]
     */
    public function getAllDiggers ()
    {
        $digs = [];
        
        foreach ($this->getGlobalDiggers() as $digId) {
            $digs[$digId] = Config::getDigger($digId);
        }
        
        foreach ($this->getSpecificDiggers() as $digModel) {
            $digs[$digModel->getId()] = $digModel;
        }
        
        return $digs;
    }
}