<?php


namespace Run;


use iConto\Interfaces\LoggerExtendInterface;
use Monolog\Logger;
use Run\Util\Tracer;

class RuntimeLog extends Logger implements LoggerExtendInterface
{
    const LOGGER_NAME_CONTEXT_KEY = 'loggerName';
    
    const LOG_LEVEL_RUNTIME = 'RUNTIME';
    
    protected $context = [];
    
    /**
     * @var \iConto\Logger
     */
    private $remoteLogger;
    
    /**
     * @var Tracer
     */
    private $tracer;
    
    public function __construct($name = 'RunCore', $handlers = array(), $processors = array())
    {
        parent::__construct($name, $handlers, $processors);
    }
    
    public function runtime ($message, array $context = array()) 
    {
        $this->addRecord(self::LOG_LEVEL_RUNTIME, $message, $context);
    }
    
    
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return null
     */
    public function addRecord($level, $message, array $context = array()) : boolean
    {
        $context += $this->context;
        
        if ($level != self::LOG_LEVEL_RUNTIME && $this->remoteLogger) {
            $this->remoteLogger->addRecord($level, $message, $context);
        }
        
        $msg = '';
        if ($context) {
            $msg = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        
        if (is_numeric($level)) {
            $level = self::getLevelName($level);
        }

        $msg = date('[Y-m-d H:i:s] '). $this->getName() .'.'. $level.': '. $message ." ".trim($msg);
        
        static $stdout;
        !$stdout && $stdout = fopen('php://stdout', 'w');
        
        fwrite($stdout, $msg."\n");
    }
    
    public function catchErrors () 
    {
        $this->tracer = new Tracer();
        $this->tracer->catchErrors(E_ALL ^ E_STRICT ^ E_DEPRECATED, [$this, 'addWarning']);
    }
    
    /**
     * @param \iConto\Logger $remoteLogger
     */
    public function setRemoteLogger($remoteLogger)
    {
        $this->remoteLogger = $remoteLogger;
    }
    
    /**
     * Заморозка параметра в контексте
     *
     * @param string $param
     * @param mixed $value
     */
    public function freeze($param, $value)
    {
        $this->context[$param] = $value;
    }
    
    /**
     * Разморозка параметра в контексте
     *
     * @param string $param
     *
     * @return void
     */
    public function unfreeze($param)
    {
        unset($this->context[$param]);
    }
    
    
    public function getFromContext ($key, $default = null)
    {
        return isset($this->context[$key]) ? $this->context[$key] : $default;
    }
    
    /**
     * Returns logger name
     *
     * @return string
     */
    public function getLoggerName()
    {
        return $this->getFromContext(self::LOGGER_NAME_CONTEXT_KEY, ''); 
    }
    
    /**
     * Sets logger name
     *
     * @param string $loggerName
     */
    public function setLoggerName($loggerName)
    {
        return $this->freeze(self::LOG_LEVEL_RUNTIME, $loggerName);
    }
}