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
}