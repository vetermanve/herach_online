<?php

namespace App\Base\Run\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Run\Storage\LogStorage;
use Storage\StorageProto;
use Throwable;

/**
 * For grouping logs of single request session
 * 
 * Class LogSessionHandler
 */
class StorageRequestLogsHandler extends AbstractProcessingHandler implements LogHandlerInterface
{
    protected $logs = [];
    protected $logsLimit = 1000;
    protected $storage;
    protected $init;

    public function __construct(\Closure $fn, int $level = Logger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->init = $fn;
    }

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

    public function flushLogs($key)
    {
        try {
            $this->getStorage()->write()->insert($key, $this->logs, __METHOD__);
        } catch (Throwable $throwable) {
            trigger_error("Cannot flush session logs to filesystem: ".$throwable->getMessage(), E_USER_WARNING);
        }
        
        $this->logs = [];
    }

    /**
     * @return StorageProto
     */
    public function getStorage()
    {
        if(!$this->storage) {
            $this->storage = ($this->init)();
        }

        return $this->storage;
    }
}