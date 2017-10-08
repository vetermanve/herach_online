<?php


namespace Statist\Processing;


use Statist\Stats;

class DataSplit extends DataUserProto
{
    /**
     * Данные которые переданы вместе c companyId будем дописывать так же и основную статсу
     */
    public function copyAllCompanyDataToGlobalStats ()
    {
        $globalData = [];
        $globalDataCompany = [Stats::ST_COMPANY_ID => 0];
        
        foreach ($this->dataContainer->data as $id => &$rec) {
            // временный хак, можно будет убрать после релиза [@added 24.05.16]
            if (!isset($rec[Stats::ST_COMPANY_ID])) {
                $rec[Stats::ST_COMPANY_ID] = 0;
            } elseif ($rec[Stats::ST_COMPANY_ID] > 0) {
                $globalData[] = $globalDataCompany + $rec;
            }
        }
    
    
        $this->dataContainer->addData($globalData);
        return $this;
    }
    
    public function splitDataByCompany () 
    {
        foreach ($this->dataContainer->data as $id => &$rec) {
            $this->dataContainer->dataByCompany
            [$rec[Stats::ST_COMPANY_ID]]
            [$rec[Stats::ST_FIELD]]
            [$id] = $rec;
        }
        
        return $this;
    }
    
    public function splitUserIdsByCompany ()
    {
        foreach ($this->dataContainer->data as $id => &$rec) {
            $this->dataContainer->userIdsByCompany
            [$rec[Stats::ST_COMPANY_ID]]
            [$rec[Stats::ST_FIELD]]
            [$rec[Stats::ST_USER_ID]] = $rec[Stats::ST_USER_ID];
        }
        
        return $this;
    }
}