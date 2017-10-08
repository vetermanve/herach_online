<?php


namespace Statist\Processing;


use Statist\Stats;
use Statist\Stats\AbstractStats;

class DataFilter extends DataUserProto
{
    /**
     * @var AbstractStats[]
     */
    protected $stats = [];
    
    /**
     * Индекс филдов которые поддерживают статистики
     * 
     * @var array
     */
    protected $statsFieldsIdx = [];
    
    protected $filteredCount = 0;
    
    public function filterNotExRecords () 
    {
        $this->fillStatsFieldsIdx();
        foreach ($this->dataContainer->data as $id => &$rec) {
            if(!isset($this->statsFieldsIdx[$rec[Stats::ST_FIELD]])) {
                unset($this->dataContainer->data[$id]);
                $this->filteredCount++;
            }
        }
        
        return $this;
    }
    
    /**
     * @param AbstractStats[] $stats
     */
    public function setStats($stats)
    {
        $this->stats = $stats;
        
        return $this;
    }
    
    protected function fillStatsFieldsIdx() {
        if (!$this->statsFieldsIdx) {
            foreach ($this->stats as $stat) {
                $this->statsFieldsIdx += array_flip($stat->getFields());
            }
        }
    }
    
    /**
     * @return int
     */
    public function getFilteredCount()
    {
        return $this->filteredCount;
    }
    
    /**
     * @return array
     */
    public function getStatsFieldsIdx()
    {
        return $this->statsFieldsIdx;
    }
}