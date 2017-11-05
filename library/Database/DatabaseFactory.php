<?php


namespace Database;


use Mu\Env;

class DatabaseFactory
{
    public function getDatabaseByResource ($resource) : DatabaseAdapterProto 
    {
        $adapter = new PostgresJson();
        
        $host = Env::getConfig()->get('host', 'db', '127.0.0.1');
        $port = Env::getConfig()->get('port', 'db', '5432');
        
        $context = new DatabaseContext();
        $context->fill([
            DatabaseContext::HOST     => $host,
            DatabaseContext::PORT     => $port ,
            DatabaseContext::DB       => 'reanimabase',
            DatabaseContext::RESOURCE => $resource,
            DatabaseContext::PASS     => 'Master9000$$',
            DatabaseContext::USER     => 'reanimabase',
        ]);
        
        $context->set(DatabaseContext::RESOURCE, $resource);
        
        $adapter->setContext($context);
        
        return $adapter;
    }
}