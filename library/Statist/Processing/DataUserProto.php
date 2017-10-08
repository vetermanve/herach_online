<?php


namespace Statist\Processing;


class DataUserProto
{
    /**
     * @var DataContainer
     */
    protected $dataContainer;
    
    /**
     * DataSplit constructor.
     *
     * @param DataContainer $dataContainer
     */
    public function __construct(DataContainer $dataContainer) { $this->dataContainer = $dataContainer; }
    
    
    /**
     * @param DataContainer $dataContainer
     */
    public function setDataContainer($dataContainer)
    {
        $this->dataContainer = $dataContainer;
    }
    
    /**
     * @return DataContainer
     */
    public function getDataContainer()
    {
        return $this->dataContainer;
    }
}