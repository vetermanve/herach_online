<?php


namespace Storage\Data;


abstract class DataAdapterProto implements DataAdapterInterface
{
    protected $primaryKey = 'id';
    
    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }
    
    /**
     * @param string $primaryKey
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
    }
    
}