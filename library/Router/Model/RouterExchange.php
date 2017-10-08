<?php


namespace Router\Model;


use Router\Model\RouterChannel;
use Router\Model\RouterServer;
use Router\Model\RouterModuleProto;

class RouterExchange extends RouterModuleProto
{
    /**
     * @var RouterChannel
     */
    private $channel;
    
    /**
     * @var string
     */
    private $exchangeName = '';
    
    /**
     * @var \AMQPExchange
     */
    public $amqpExchange;
    
    public function setup () 
    {
        $this->loadChannel();
        $this->loadExchange();
    }
    
    private function loadExchange() {
        try {
            $exchange = new \AMQPExchange($this->channel->amqpChannel);
            $exchange->setName($this->exchangeName);
        
            if ($this->exchangeName) {
                $exchange->setType(AMQP_EX_TYPE_DIRECT);
                $exchange->setFlags(AMQP_DURABLE);
                $exchange->declareExchange();
            }
            
            $this->amqpExchange = $exchange;
        } catch (\Exception $exception) {
            $this->_reportProblem($exception->getMessage());
        }
    }
    
    private function loadChannel() {
        $this->channel = $this->server->getChannel($this->thread);
    }
    
    public function recovery()
    {
        $this->loadChannel();
        $this->channel->recovery();
        $this->loadExchange();
    }
}