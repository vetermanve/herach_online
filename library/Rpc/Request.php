<?php


namespace Rpc;


use Monolog\Logger;

class Request
{
    const TYPE_UNISEARCH = 'unisearch';
    // system queue for parse controller
    const TYPE_PARSE_CHUNKS_QUEUE = 'chunks';
    
    /* request params */
    private $_service    = RpcServices::SERVICE_UNIVERSE;
    private $_type       = RpcServices::TYPE_CONTROLLER;
    private $_module     = '';
    private $_controller = '';
    private $_method     = '';
    private $_params     = [];
    private $_timeout    = 30;
    private $_requestSentTime;
    
    /* result container */
    protected $_result = [];
    
    /* result state */
    protected $_isDataRead = false;
    
    /* object state */
    private $_isCast     = false;
    private $_status;
    private $_callResult = [];
    
    /* result read props */
    
    /**
     * @var string
     */
    private $_requestQueueName;
    private $_correlationId;
    
    private $_logger;
    private $_rpcRequestId;
    private $_profiling = [];
    
    public function __construct() {
        $this->_rpcRequestId = RpcMaster::i()->registerRpc($this);
    }
    
    /**
     * @return Logger
     */
    public function log () 
    {
//        if (!$this->_logger) {
//            $this->_logger = Env::getLogger();    
//        }
        return $this->_logger;
    }
    
    /**
     * @return RpcTransports
     */
    public function getTransportMgr () 
    {
        static $transportMgr;
        !$transportMgr && $transportMgr = new RpcTransports();
        return $transportMgr;
    }
    
    /**
     * @return string
     */
    public function getRequestQueueName()
    {
        if (!$this->_requestQueueName) {
            $queueMaster = $this->getTransportMgr();
            
            if ($this->_controller && $this->_module) {
                $this->_requestQueueName = $queueMaster->getQueueName($this->_controller, $this->_module, $this->_service, $this->_type);
            } 
//            else {
//                $this->_requestQueueName = $queueMaster->getOldQueueName($this->_service);
//                $connectionInfo = $queueMaster->getOldServiceConnectionInfo($this->_service);
//                
//                if ($connectionInfo) {
//                    list($host, $port) = $connectionInfo;
//                    Env::getRouter()->registerQueue($this->_requestQueueName, $host, $port);        
//                }
//            }
        }
        
        return $this->_requestQueueName;
    }
    
    
    public function send ()
    {
        $this->_requestSentTime = microtime(1);
        
        $message = [
            0, // Тип запроса
            0, // ID запроса
            $this->_method, // Название удаленной процедуры
            $this->_params, // Аргументы для вызова процедуры
        ];
        
        $this->getRequestQueueName();
        
        $this->_correlationId = Env::getRouter()->publish($message, $this->_requestQueueName, !$this->_isCast);

        $this->log()
            ->debug('RpcRequest send ' . $this->_controller . ':' . $this->_module . ':' . $this->_method, [
                'to_queue' => $this->_requestQueueName,
                'is_cast' => $this->_isCast,
                'method' => $this->_method,
                'params' => $this->_params,
                'service' => $this->_service,
                'type' => $this->_type,
            ]);
    }
    
    public function fetch () 
    {
        if ($this->_isDataRead) {
            return $this->_result; 
        }
        
        if ($this->_isCast) {
            $this->_isDataRead = true;
            return $this->_result;
        }
        
        if (!$this->_requestQueueName || !$this->_correlationId) {
            throw new \RuntimeException('Request queue or correlationId not defined on result fetch');
        }
        
        $this->_callResult = Env::getRouter()->readResult($this->_requestQueueName, $this->_correlationId, $this->_timeout);
    
        if (isset($this->_callResult['__profiling'])) {
            $this->_profiling = array_merge($this->_profiling, (array)$this->_callResult['__profiling']);
        }
    
        if (isset($this->_callResult['result']['__profiling'])) {
            $this->_profiling = array_merge($this->_profiling, (array)$this->_callResult['result']['__profiling']);
            unset($this->_callResult['result']['__profiling']);
        }
    
        $this->_isDataRead = true;
        
        if ($this->_callResult === null) {
            $this->_result = [];
            $this->_status = 1;
            $this->log()->error('RpcRequestSent: Empty response on fetch', [
                'controller' => $this->_controller,
                'module' => $this->_module,
                'method' => $this->_method,
                'fwdQueue' => $this->_requestQueueName,
                'correlationId' => $this->_correlationId,
                'timeout' => $this->_timeout,
                'sentTime' => $this->_requestSentTime,
            ]);
    
            $this->_profiling['__callTime'] = round((microtime(1) - $this->_requestSentTime), 4).'';
            
            throw new \RuntimeException(['message' => 'no response from service on queue: '.$this->_requestQueueName]);
        } 
        
        $this->_status = 0;
        
        $this->_profiling['__callTime'] = [
            'process' => round($this->_callResult['time'],4), 
            'get' => round((microtime(1) - $this->_requestSentTime), 4),
            'unpack' => isset($this->_callResult['__unpack']) ? $this->_callResult['__unpack'] : null,
        ];
    
        if ($this->_callResult['status'] !== 1) {
            if (isset($this->_callResult['error'])) {
                $this->log()
                    ->error('Rpc universe call ' . $this->_module . ':' . $this->_controller . '->' . $this->_method . ' return error',
                        $this->_callResult['error']);
                throw new \RuntimeException($this->_callResult['error']);
            } else {
                throw new \ErrorException();
            }
        }
        
        $this->_result = isset($this->_callResult['result']) ? $this->_callResult['result'] : null;
        
        $this->log()->debug('RpcRequest response '.$this->_controller.':'.$this->_module.':'.$this->_method, [
            'count' => count($this->_result),
            'time' => round(microtime(1) - $this->_requestSentTime, 6),
        ]);
        
        return $this->_result;
    }
    
    /**
     * @param string $controller
     *
     * @return Request
     */
    public function setController($controller)
    {
        $this->_controller = $controller;
        
        return $this;
    }
    
    /**
     * @param string $method
     *
     * @return Request
     */
    public function setMethod($method)
    {
        $this->_method = $method;
        
        return $this;
    }
    
    /**
     * @param array $params
     *
     * @return $this
     */
    public function setParams($params)
    {
        $this->_params = $params;
    
        return $this;
    }
    
    /**
     * @param int $timeout
     *
     * @return Request
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = $timeout;
        
        return $this;
    }
    
    /**
     * @return array
     */
    public function getData()
    {
        if (!$this->_isDataRead) {
            $this->fetch();
        }
        
        return $this->_result;
    }
    
    /**
     * @param array $result
     */
    public function setResult($result)
    {
        $this->_result = $result;
    }
    
    /**
     * @param boolean $isCast
     *
     * @return Request
     */
    public function setIsCast($isCast)
    {
        $this->_isCast = $isCast;
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->_status;
    }
    
    /**
     * @return string
     */
    public function getModule()
    {
        return $this->_module;
    }
    
    /**
     * @param string $module
     *
     * @return $this
     */
    public function setModule($module)
    {
        $this->_module = $module;
        
        return $this;
    }
    
    /**
     * @return array
     */
    public function getCallResult()
    {
        return $this->_callResult;
    }
    
    /**
     * @return string
     */
    public function getController()
    {
        return $this->_controller;
    }
    
    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }
    
    public function getName () 
    {
        return '[' . $this->_rpcRequestId . '] ' . $this->_module . ':' . $this->_controller . '->' . $this->_method;
    }
    
    /**
     * @return mixed
     */
    public function getRpcRequestId()
    {
        return $this->_rpcRequestId;
    }
    
    public function getProfiling() {
        return $this->_profiling;
    }
    
    /**
     * @param string $service
     *
     * @return Request
     */
    public function setService($service)
    {
        $this->_service = $service;
        
        return $this;
    }
    
    /**
     * @param string $type
     *
     * @return Request
     */
    public function setType($type)
    {
        $this->_type = $type;
        
        return $this;
    }
}