<?php

namespace Statist\Digger;

use iConto\ServicesTrait;

abstract class AbstractDigger {
    
    use ServicesTrait;
    
    /**
     * @var \Statist\Dater;
     */
    protected $processor;
    
    protected $companyId;
    
    protected $ids;
    protected $fieldsIdx = [];
    protected $result = [];
    
    abstract public function digData();
    abstract public function getId();
    abstract public function getDataRange();
    abstract public function getName();
    
    /**
     * @return \Statist\Processor
     */
    public function getProcessor()
    {
        return $this->processor;
    }
    
    /**
     * @param \Statist\Processor $processor
     */
    public function setProcessor($processor)
    {
        $this->processor = $processor;
    }
    
    /**
     * @return mixed
     */
    public function getIds()
    {
        return $this->ids;
    }
    
    /**
     * @param mixed $ids
     */
    public function setIds($ids)
    {
        $this->ids = $ids;
    }
    
    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }
    
    /**
     * @param mixed $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }
    
    public function getClassName () 
    {
        $class = get_called_class();
        $pos = strrpos($class, '\\');
        return substr($class, $pos + 1);
    }
    
    public function addFields ($fields) 
    {
        $this->fieldsIdx += array_flip($fields);   
    }
    
    /**
     * @return mixed
     */
    public function getFieldsIdx()
    {
        return $this->fieldsIdx;
    }
    
    /**
     * @param mixed $fieldsIdx
     */
    public function setFieldsIdx($fieldsIdx)
    {
        $this->fieldsIdx = $fieldsIdx;
    }
    
    public function isShowName () 
    {
        return true;
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
     *
     * @return $this
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
        return $this;
    }
    
    public function isHideSubtitle() 
    {
        return false;
    }
}
