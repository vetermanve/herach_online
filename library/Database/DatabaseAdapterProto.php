<?php


namespace Database;


use Modular\ModularContainerProto;

abstract class DatabaseAdapterProto
{
    /**
     * @var DatabaseContext
     */
    protected $context;
    
    abstract public function getByIds($ids);
    
    abstract public function search($filter, $count = 500);
    
    abstract public function getAll($limit = 100, $offset = 0);
    
    abstract public function set($id, $data);
    
    /**
     * @return DatabaseContext
     */
    public function getContext(): DatabaseContext
    {
        return $this->context;
    }
    
    /**
     * @param DatabaseContext $context
     */
    public function setContext(DatabaseContext $context)
    {
        $this->context = $context;
    }
}