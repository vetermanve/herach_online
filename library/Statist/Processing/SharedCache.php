<?php


namespace Statist\Processing;


class SharedCache
{
    const TIME_MARK_DAY   = 'day';
    const TIME_MARK_HOUR  = 'hour';
    const TIME_MARK_MONTH = 'month';
    
    protected $timeMarks = [];
    
    /**
     * Статический кэш crc филдов
     *
     * @var array
     */
    private $fieldsCrc = [];
    
    
    public function getDefaultTimeMarks ($time)
    {
        if (!isset($this->timeMarks[$time])) {
            $saveTimeZone = date_default_timezone_get();
            date_default_timezone_set('UTC');
            
            $d = date('d', $time);
            $m = date('m', $time);
            $y = date('Y', $time);
    
            $this->timeMarks[$time] = [
                self::TIME_MARK_HOUR  => [1.1, (int)ceil($time / 3600) * 3600],   // this hour start
                self::TIME_MARK_DAY   => [25,  (int)mktime(0, 0, 1, $m, $d, $y)], // this day start + 1 second
                self::TIME_MARK_MONTH => [750, (int)mktime(0, 0, 2, $m, 1, $y)],  // this month start + 2 seconds
            ];
    
            date_default_timezone_set($saveTimeZone);
        }
        
        return $this->timeMarks[$time];
    }
    
    public function getFieldCrc($field) {
        if (!isset($this->fieldsCrc[$field])) {
            $this->fieldsCrc[$field] = sprintf("%u", crc32($field));
        }
        
        return $this->fieldsCrc[$field];
    }
    
    
    protected $loaders;
    
    /**
     * @param $loaderId
     * @return \Statist\Loader\AbstractLoader
     */
    public function getLoader($loaderId)
    {
        if(!isset($this->loaders[$loaderId])) {
            $modelName = class_exists('Statist\\Loader\\'.$loaderId) ? 'Statist\\Loader\\'.$loaderId : false;
            $this->loaders[$loaderId]= $modelName ? new $modelName($this) : new EmptyLoader($this);
        }
        
        return $this->loaders[$loaderId];
    }
    
    
    
}