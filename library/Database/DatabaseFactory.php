<?php


namespace Database;


class DatabaseFactory
{
    
    
    public function getDatabaseByResource ($resource) : DatabaseAdapterProto 
    {
        $adapter = new PostgresJson();
        
        $context = new DatabaseContext();
        $context->set('');
        
        return $adapter;
    }
}