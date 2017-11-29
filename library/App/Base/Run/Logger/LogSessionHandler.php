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
class LogSessionHandler extends AbstractProcessingHandler
{
    protected $logs = [];
    
    protected $logsLimit = 1000;
    
    private $redisClient;
    
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
            $this->redisClient->hMset($key, $this->logs);
            $this->redisClient->expire($key, 600);
        } catch (Throwable $throwable) {
            trigger_error("Cannot flush session logs to redis: ".$throwable->getMessage(), E_USER_WARNING);
        }
        
        $this->logs = [];
    }
    
    /**
     * @param \Redis $redis  The redis instance
     * @param bool|int              $level  The minimum logging level at which this handler will be triggered
     * @param bool                  $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($redis, $level = Logger::DEBUG, bool $bubble = true)
    {
        if (!($redis instanceof \Redis)) {
            throw new \InvalidArgumentException('Redis instance required');
        }
        
        $this->redisClient = $redis;
        parent::__construct($level, $bubble);
    }
}