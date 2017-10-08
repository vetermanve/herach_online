<?php


namespace Statist\Processing;


class DataContainer
{
    public $data = [];
    
    public $dataByCompany = [];
    public $userIdsByCompany = [];
    
    public function dataCount () 
    {
        return count($this->data);
    }
    
    public function addData ($dataPack) 
    {
        $this->data = array_merge($this->data, $dataPack);
    }
    
    public function setData ($data) 
    {
        $this->data = $data;
    }
    
    /**
     * @return array
     */
    public function getDataByCompanyCount()
    {
        return count($this->dataByCompany);
    }
    
    public function getCompanyIds()
    {
        return array_keys($this->dataByCompany);
    }
    
}