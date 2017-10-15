<?php

namespace Statist;

use Mu\Amqp\Message;
use Mu\Env;
use Mu\Transport;
use Mu\Transport\AmqpSimple;

/**
 * Class Stats
 * 
 * Класс, через который должно быть удобвно инерементить статистику,
 * из этого класса статистика уходит в Кролика из кролика она достается Лоадером, 
 * Лоадер передает ее в Процессор, Процессор сохраняет данные в базу и Редис
 * 
 * @package Statist
 */
class Stats  {
    
    const QUEUE_DEFAULT = 'stats.main';
    const EXCHANGE_DEFAULT =  'stats2';
    
    const ST_FIELD      = 0;
    const ST_USER_ID    = 1;
    const ST_COUNT      = 2;
    const ST_TIME       = 3;
    const ST_CONTEXT    = 4;
    const ST_COMPANY_ID = 5;
    
    const DATA_UNQ = 'unqId';
    const DATA_CUSTOM_TIME_MARK = 'cTimeMark';
    const DATA_LONG_STORE = 'longStore';
    
    protected $userId;
    protected $companyId = 0;
    
    protected $lastResult;
    
    protected $queueName = self::QUEUE_DEFAULT;
    
    /**
     * @var AmqpSimple
     */
    protected static $transport;
    
    /**
     * @var \Mu\Amqp\Connection
     */
    protected static $connection;
    
    /**
     * Stats constructor.
     *
     * @param int $userId
     * @param int $companyId
     */
    function __construct($userId = 0, $companyId = 0)
    {
        $this->userId = $userId;
        $this->companyId = $companyId;
    }
    
    /**
     * @return Stats
     */
    public static function i()
    {
        static $i;
        !$i && $i = new self();
        return $i;
    }
    
    /**
     * @param $userId
     *
     * @return Stats
     */
    public function forUser($userId)
    {
        return new self($userId);
    }
    
    /**
     * @return AmqpSimple
     */
    public function loadTransport () 
    {
        self::$connection = Env::getQueue();
    }
    
    /**
     * Отправить эвент с userId и companyId из контекста
     * 
     * @param $fields
     * @param int $count
     * @param bool $forceTime
     * @param array $context
     * 
     * @return $this
     */
    public function cEvent($fields, $count = 1, $forceTime = null, $context = []) 
    {
        $message = [
            self::ST_USER_ID    => $this->userId,
            self::ST_COUNT      => $count,
            self::ST_TIME       => ($forceTime ? $forceTime : time()),
            self::ST_CONTEXT    => $context,
            self::ST_COMPANY_ID => $this->companyId
        ];
        
        $this->sendMessage($fields, $message);
        
        return $this;
    }
    
    /**
     * Отправить эвент с явной передачей userId и companyId
     * 
     * @param       $fields
     * @param       $userId
     * @param       $companyId
     * @param int   $count
     * @param null  $forceTime
     * @param array $context
     *
     * @return $this
     */
    public function event($fields, $userId, $companyId, $count = 1, $forceTime = null, $context = [])
    {
        $message = [
            self::ST_USER_ID    => $userId,
            self::ST_COUNT      => $count,
            self::ST_TIME       => ($forceTime ? $forceTime : time()),
            self::ST_CONTEXT    => $context,
            self::ST_COMPANY_ID => $companyId
        ];
        
        $this->sendMessage($fields, $message);
        
        return $this;
    }
    
    public function eventWithContext($fields, $userId, $companyId, $context = [], $count = 1, $forceTime = null)
    {
        $message = [
            self::ST_USER_ID    => $userId,
            self::ST_COUNT      => $count,
            self::ST_TIME       => ($forceTime ? $forceTime : time()),
            self::ST_CONTEXT    => $context,
            self::ST_COMPANY_ID => $companyId
        ];
        
        $this->sendMessage($fields, $message);
        
        return $this;
    }
    
    private function sendMessage($fields, $message) {
        !self::$connection && $this->loadTransport();
    
        foreach ((array)$fields as $field) {
            self::$connection->send(new Message([self::ST_FIELD => $field] + $message), $this->queueName);    
        }
    }
    
    /**
     * @return string
     */
    public function getQueueName()
    {
        return $this->queueName;
    }
    
    /**
     * @param string $queueName
     */
    public function setQueueName($queueName)
    {
        $this->queueName = $queueName;
    }
}
