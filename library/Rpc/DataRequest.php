<?php


namespace Rpc;


class DataRequest extends Request implements \ArrayAccess, \Countable, \IteratorAggregate
{
    
    public function offsetExists($offset)
    {
        if (!$this->_isDataRead) {
            $this->fetch();
        }
        
        return array_key_exists($offset, $this->_result);
    }
    
    public function offsetGet($offset)
    {
        if (!$this->_isDataRead) {
            $this->fetch();
        }
        
        return $this->_result[$offset];
    }
    
    public function offsetSet($offset, $value)
    {
        if (!$this->_isDataRead) {
            $this->fetch();
        }
        
        return $this->_result[$offset] = $value;
    }
    
    public function offsetUnset($offset)
    {
        if (!$this->_isDataRead) {
            $this->fetch();
        }
    
        unset($this->_result[$offset]);
    }
    
    public function count()
    {
        if (!$this->_isDataRead) {
            $this->fetch();
        }
        
        return count($this->_result);
    }
    
    public function getIterator()
    {
        if (!$this->_isDataRead) {
            $this->fetch();
        }
    
        return new \ArrayIterator((array)$this->_result);
    }
    
    function __toString()
    {
        return is_string($this->_result) ? $this->_result : json_encode($this->_result);
    }
    
    
}