<?php


namespace Router\Model;


class RouterChannel extends RouterModuleProto
{
    /**
     * @var \AMQPChannel
     */
    public $amqpChannel;
    
    /**
     * @var RouterConnection
     */
    private $connection;
    
    public function setup()
    {
        // запрашиваем коннекшн
        $this->loadConnection();
        // создаем канал
        $this->createChannel();
    }
    
    public function recovery()
    {
        // перезапрашиваем коннекшн
        $this->loadConnection();
        // рекаверим коннекшн
        $this->connection->recovery();
        // пересоздаем канал
        $this->createChannel();
    }
    
    private function createChannel() {
        try {
            $this->amqpChannel = new \AMQPChannel($this->connection->amqpConnection);
        } catch (\Exception $exception) {
            $this->_reportProblem($exception->getMessage());
        }
    }
    
    private function loadConnection() {
        $this->connection = $this->server->getConnection($this->thread);
    }
}