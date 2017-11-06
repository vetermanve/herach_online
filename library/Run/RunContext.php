<?php


namespace Run;


use Modular\ModularContextProto;

class RunContext extends ModularContextProto
{
    const IDENTITY = 'identity';
    
    const HOST = 'host';
    
    const ENV_DEBUG = 'debug';
    
    const CLOUD       = 'cloud';
    const DATA_CENTER = 'dc';
    
    const GLOBAL_CONFIG = 'global_config';
    
    /* AMQP CONSUMING */
    const AMQP_REQUEST_CLOUD_HOST = 'amqp_request_host';
    const AMQP_REQUEST_CLOUD_PORT = 'amqp_request_port';
    
    const AMQP_RESULT_CLOUD_HOST  = 'amqp_host_outgoing';
    const AMQP_INSIDE_CLOUD_HOST  = 'amqp_host_inside';
    
    const QUEUE_INCOMING = 'queue_name_incoming';
    
    const REQUEST_PROFILING_ENABLED = 'is_profiling';
    
    const HTTP_RESOURCE_OVERRIDE = 'resource-override';
    const IS_SECURE_CONNECTION = 'is_secure_connection';
    
    private $activation = [];
    
    public function setKeyActivation($key, $callable) {
        $this->activation[$key] = $callable;
    }
    
    public function &getLink($key, $writeDefault)
    {
        if (isset($this->activation[$key])) {
            $writeDefault = $this->activation[$key]();
            unset($this->activation[$key]);
        }
        
        return parent::getLink($key, $writeDefault);
    }
    
}