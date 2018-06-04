<?php

namespace App\Base\Run\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Throwable;

/**
 * For grouping logs of single request session
 * 
 * Class LogSessionHandler
 */
class LogSessionHandler extends AbstractProcessingHandler implements LogHandlerInterface
{
    protected $logs = [];
    
    protected $logsLimit = 1000;
    
    /**
     * @var \Redis
     */
    private $redisClient;
    
    /**
     * @var callable
     */
    private $redisBootstrap;
    
    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     *
     * @return void
     */
    protected function write(array $record)
    {
        $context = '';
        if ($record['context']) {
            $context = json_encode($record['context'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        
        $this->logs[] = date('[Y-m-d H:i:s] '). $record['channel'] .'.'. $record['level_name'].': '. $record['message'] ." ".trim($context);
        if (count($this->logs) > $this->logsLimit) {
             array_shift($this->logs);
        }
    }
    
    /**
     * @return array
     */
    public function flushLogs($key)
    {
        try {
            $this->_redis()->hMset($key, $this->logs);
            $this->_redis()->expire($key, 600);
        } catch (Throwable $throwable) {
            trigger_error("Cannot flush session logs to redis: ".$throwable->getMessage(), E_USER_WARNING);
        }
        
        $this->logs = [];
    }
    
    /**
     * @param callable      $redisBootstrap
     * @param bool|int $level  The minimum logging level at which this handler will be triggered
     * @param bool     $bubble Whether the messages that are handled can bubble up the stack or not
     *
     */
    public function __construct($redisBootstrap, $level = Logger::DEBUG, bool $bubble = true)
    {
        $this->redisBootstrap = $redisBootstrap;
        parent::__construct($level, $bubble);
    }
    
    /**
     * @return \Redis
     */
    private function _redis() {
        if ($this->redisClient === null) {
            $this->redisClient = call_user_func($this->redisBootstrap);
        }
        
        return $this->redisClient;
    }
}