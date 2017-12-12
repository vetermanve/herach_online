<?php


namespace App\Rest\Storage\DataAdapter;


use Storage\Data\DataAdapterProto;
use Storage\Request\StorageDataRequest;

class JBaseDataAdapter extends DataAdapterProto
{
    const READ_ACCESS = 'r';
    const CREATE_ACCESS = 'w';
    
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
        
        if ($method === self::READ_ACCESS && !file_exists($filePath)) {
            return null;
        }
            
        return fopen($filePath, $method);    
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
                $res = $self->getPointer($id, self::CREATE_ACCESS);
                return fwrite($res, $this->_packData($bind));
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
        // TODO: Implement getSearchRequest() method.
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