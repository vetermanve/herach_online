<?php


namespace Modular;


class ModularContainerProto
{
    public $data    = [];
    public $results = [];
    
    public function addData($data)
    {
        $this->_checkData($data);
        
        $this->data = array_merge($this->data, $data);
    }
    
    public function setData($data)
    {
        $this->_checkData($data);
        
        if ($this->data) {
            trigger_error('Unexpected rewriting data', E_USER_WARNING);
        }
        
        $this->data = $data;
    }
    
    public function rewriteData($data)
    {
        $this->_checkData($data);
        
        $this->data = $data;
    }
    
    private function _checkData(&$data)
    {
        if (!is_array($data)) {
            trigger_error('Data not is an array, not rewrote', E_USER_WARNING);
            $data = [];
        }
    }
    
    public function getDataCount()
    {
        return count($this->data);
    }
}