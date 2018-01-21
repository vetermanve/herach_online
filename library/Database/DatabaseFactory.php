<?php


namespace Database;


use Mu\Env;

class DatabaseFactory
{
    /**
     * @param $resource
     *
     * @return DatabaseAdapterProto
     */
    public function getDatabaseByResource ($resource) : DatabaseAdapterProto 
    {
        $adapter = new PostgresJson();
        
        $adapter->setContext($this->getDatabaseContextByResource($resource));
        
        return $adapter;
    }
    
    /**
     * @param $resource
     *
     * @return DatabaseContext
     */
    public function getDatabaseContextByResource ($resource) : DatabaseContext
    {
        $host = Env::getLegacyConfig()->get('host', 'db', '127.0.0.1');
        $port = Env::getLegacyConfig()->get('port', 'db', '5432');
    
        $context = new DatabaseContext();
        $context->fill([
            DatabaseContext::HOST     => $host,
            DatabaseContext::PORT     => $port ,
            DatabaseContext::DB       => 'reanimabase',
            DatabaseContext::RESOURCE => $resource,
            DatabaseContext::PASS     => 'Master9000$$',
            DatabaseContext::USER     => 'reanimabase',
        ]);
        
        return $context;
    }
}