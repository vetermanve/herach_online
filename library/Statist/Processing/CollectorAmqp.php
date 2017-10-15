<?php


namespace Statist\Processing;

use AMQPEnvelope;
use Mu\Amqp\Queue;
use Mu\Env;

use Statist\Stats;

/**
 * Class Collector
 * 
 * Это класс который занимается забором статистики из очереди.
 * 
 * 
 * @package Statist
 */
class CollectorAmqp extends DataUserProto {
    
    /**
     * Сколько мы выгребаем рекодов статистики из очереди за один запуск
     * 
     * @var int
     */
    private $chunkSize = 100;
    
    /**
     * Посылки AMQP
     * 
     * @var AMQPEnvelope[]
     */
    private $envelopes = [];
    
    /**
     * Подтверждать ли получение сразу после получения пакета 
     * 
     * @var bool
     */
    private $confirmDeliveryAfterReceive = false;
    
    /**
     * Имя рабочей очереди
     * 
     * @var string
     */
    private $queueName = Stats::QUEUE_DEFAULT;
    
    /**
     * Инстанс рабочей очереди
     * 
     * @var Queue
     */
    private $queue;
    
    
    public function getQueue () 
    {
        if (!$this->queue) {
            $connection = Env::getQueue();
            $this->queue = $connection->getQueue($this->queueName);    
        }
        
        return $this->queue;
    }
    
    public function collectData() 
    {
        $currentProcessed = 0;
        
        $autoConfirm = $this->confirmDeliveryAfterReceive ? AMQP_AUTOACK : AMQP_NOPARAM;
        $queue = $this->getQueue();
        
        while ($envelope = $queue->get($autoConfirm)) {
            if (++$currentProcessed > $this->chunkSize) {
                break;
            }
            
            $this->addEnvelope($envelope);
        }
        
        return $this;
    }
    
    public function confirmAllMessages () 
    {
        foreach ($this->envelopes as $envelope) {
            $this->queue->ack($envelope->getDeliveryTag());
        }
        
        return $this;
    }
    
    private function addEnvelope(AMQPEnvelope $envelope)
    {
        $this->envelopes[] = $envelope;
        $this->dataContainer->data[] = $this->decode($envelope->getBody());
    }
    
    private function decode ($body) 
    {
        return json_decode($body, true);
    }
    
    /**
     * @return AMQPEnvelope[]
     */
    public function getEnvelopes()
    {
        return $this->envelopes;
    }
    
    /**
     * @return boolean
     */
    public function isConfirmDeliveryAfterReceive()
    {
        return $this->confirmDeliveryAfterReceive;
    }
    
    /**
     * @param boolean $confirmDeliveryAfterReceive
     * @return $this
     */
    public function setConfirmDeliveryAfterReceive($confirmDeliveryAfterReceive)
    {
        $this->confirmDeliveryAfterReceive = $confirmDeliveryAfterReceive;
        return $this;
    }
    
    /**
     * @return int
     */
    public function getChunkSize()
    {
        return $this->chunkSize;
    }
    
    /**
     * @param int $chunkSize
     * 
     * @return $this
     */
    public function setChunkSize($chunkSize)
    {
        $this->chunkSize = $chunkSize;
        return $this;
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
     *
     * @return $this
     */
    public function setQueueName($queueName)
    {
        $this->queueName = $queueName;
        $this->queue = null;
        
        return $this;
    }
    
}