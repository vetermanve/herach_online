<?php


namespace Database;


use Nette\Database\Row;
use PDO;

class PostgresJson extends DatabaseAdapterProto
{
    private static $_connection;
    
    private $_resource = '';
    
    public function connection () 
    {
        if (!self::$_connection) {
            $host = $this->context->get(DatabaseContext::HOST, '127.0.0.1');
            $port = $this->context->get(DatabaseContext::PORT, '5432');
            $db = $this->context->get(DatabaseContext::DB);
            $user = $this->context->get(DatabaseContext::USER);
            $password = $this->context->get(DatabaseContext::PASS);
            
            $dsn = 'pgsql:dbname='.$db.';host='.$host.';port='.$port;
            self::$_connection = new \Nette\Database\Connection(
                $dsn,
                $user,
                $password,
                array(
                    PDO::ATTR_TIMEOUT => "1",
//                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                )
            );
    
            self::$_connection->connect();
            
        }
        
        $this->_resource = $this->context->get(DatabaseContext::RESOURCE);
        
        return self::$_connection;
    }
    
    
    public function getByIds ($ids) 
    {
        $this->connection();
    }
    
    public function search ($filter, $limit = 500) 
    {
        
    }
    
    public function getAll($limit = 100, $offset = 0)
    {
        $results =$this->connection()->query('SELECT * from '.$this->_resource.' LIMIT '.$limit.' OFFSET '.$offset);
        $data = $results->fetchAll();
        return $this->_unpackRows($data);
    }
    
    /**
     * @param $rows
     *
     * @return Row[]
     */
    private function _unpackRows($rows) {
        if (!$rows) {
            return [];
        }
        
        $results = [];
        foreach ($rows as $row) {
            $data = json_decode($row->data, true);
            $data['id'] = $row->id;
            $results[$data['id']] = $data;
        }
        
        return $results; 
    }
}