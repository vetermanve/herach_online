<?php


namespace App\Rest\Storage\DataAdapter;


use Storage\Data\DataAdapterProto;
use Storage\Request\StorageDataRequest;

class JBaseDataAdapter extends DataAdapterProto
{
    const READ_ACCESS = 'r';
    const CREATE_ACCESS = 'w';
    const ADD_ACCESS = 'x';
    
    const F_DATA = 'd';
    const F_TOUCHED = 't';
    
    private $dataRoot = '/tmp/jbase';
    
    private $database = 'default';
    
    private $resource;
    
    private $_dirCheckCache = [];
    
    private function _getTablePath() {
        $path = $this->dataRoot.'/'.$this->database.'/'.$this->resource.'/';
        if (isset($this->_dirCheckCache[$path])) {
            return $path;
        }
        
        if (!file_exists($path)) {
            mkdir($path, 0774, true);
            chmod($path, 0774);
        }
        
        $this->_dirCheckCache[$path] = file_exists($path);
        
        return $path;
    }
    
    public function getPointer($id, $method = self::READ_ACCESS) {
        $filePath = $this->_getTablePath().$id;
        
        $fileExists = file_exists($filePath);
        if ($method === self::READ_ACCESS && !$fileExists) {
            return null;
        }
        
        if ($method === self::ADD_ACCESS && $fileExists) {
            return null;
        }
            
        return fopen($filePath, $method);    
    }
    
    public function getAllItems() {
        $list = scandir($this->_getTablePath());
        return array_diff($list, ['.','..']);
    }
    
    /**
     * @param $id         null|int|array
     * @param $insertBind array
     *
     * @return StorageDataRequest
     */
    public function getInsertRequest($id, $insertBind)
    {
        $self = $this;
        $request = new StorageDataRequest(
            [$id, $insertBind],
            function ($id, $bind) use ($self) {
                $pointer = $self->getPointer($id, self::ADD_ACCESS);
                $res = fwrite($pointer, $this->_packData($bind));
                return $res ? $bind : null;
            }
        );
        
        return $request;
    }
    
    private function _packData($data) {
        $bind = [
            self::F_DATA => is_array($data) ? $data : [$data],
            self::F_TOUCHED => microtime(1)
        ];
        
        return json_encode($bind, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); 
    }
    
    /**
     * @param $insertBindsByKeys
     *
     * @return StorageDataRequest
     */
    public function getBatchInsertRequest($insertBindsByKeys)
    {
        // TODO: Implement getBatchInsertRequest() method.
    }
    
    /**
     * @param $id int|array
     * @param $updateBind
     *
     * @return StorageDataRequest
     */
    public function getUpdateRequest($id, $updateBind)
    {
        // TODO: Implement getUpdateRequest() method.
    }
    
    /**
     * @param $ids
     *
     * @return StorageDataRequest fetch result is array [ 'key1' => ['primary' => 'key1', ...], ... ]
     */
    public function getReadRequest($ids)
    {
        $self = $this;
        $request = new StorageDataRequest(
            [$ids],
            function ($ids) use ($self) {
                $results = [];
                foreach ($ids as $id) {
                    $pointer = $self->getPointer($id);
                    if ($pointer) {
                        $record = json_decode(stream_get_contents($pointer), true);
                        if (isset($record[self::F_DATA])) {
                            $results[$id] = $record[self::F_DATA] + [$this->primaryKey => $id];
                        }
                    } else {
                        $results[$id] = null;
                    }
                }
                
                return $results;
            }
        );
    
        return $request;
    }
    
    /**
     * @param $ids int|array
     *
     * @return StorageDataRequest
     */
    public function getDeleteRequest($ids)
    {
        // TODO: Implement getDeleteRequest() method.
    }
    
    /**
     * @param array $filter
     *
     * @param int   $limit
     * @param array $conditions
     *
     * @return StorageDataRequest
     */
    public function getSearchRequest($filter, $limit = 1, $conditions = [])
    {
        $self = $this;
        $request = new StorageDataRequest(
            [$filter],
            function ($filter) use ($self) {
                $items = $self->getAllItems();
                $results = [];
                foreach ($items as $id) {
                    $pointer = $self->getPointer($id);
                    $isOk = true;
                    if ($pointer) {
                        $record = json_decode(stream_get_contents($pointer), true);
                        foreach ($filter as $key => $value) {
                            if (!(isset($record[$key]) && $record[$key] == $value)) {
                                $isOk = false;
                            }
                        }
                        
                        if ($isOk && isset($record[self::F_DATA])) {
                            $results[$id] = $record[self::F_DATA] + [$this->primaryKey => $id];
                        }
                    }
                }
            
                return $results;
            }
        );
    
        return $request;
    }
    
    
    /**
     * @return string
     */
    public function getDataRoot(): string
    {
        return $this->dataRoot;
    }
    
    /**
     * @param string $dataRoot
     */
    public function setDataRoot(string $dataRoot)
    {
        $this->dataRoot = $dataRoot;
    }
    
    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }
    
    /**
     * @param string $database
     */
    public function setDatabase(string $database)
    {
        $this->database = $database;
    }
    
    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }
    
    /**
     * @param mixed $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }
    
    
}