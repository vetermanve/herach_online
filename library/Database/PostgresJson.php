<?php


namespace Database;


class PostgresJson extends DatabaseAdapterProto
{
    private $_connection;
    
    private $_resource = '';
    
    public function connection () 
    {
        if (!$this->_connection) {
            $host = $this->context->get(DatabaseContext::HOST);
            $db = $this->context->get(DatabaseContext::DB);
            $user = $this->context->get(DatabaseContext::USER);
            $password = $this->context->get(DatabaseContext::PASS);
            
            $dsn = 'pgsql:dbname='.$db.' host='.$host;
            $this->_connection = new \Nette\Database\Connection(
                $dsn,
                $user,
                $password
            );
            
            $this->_connection->connect();
            
            $this->_resource = $this->context->get(DatabaseContext::RESOURCE);
        }
        
        return $this->_connection;
    }
    
    
    public function getByIds ($ids) 
    {
        $this->connection();
    }
    
    public function search ($filter, $limit = 500) 
    {
        
    }
}