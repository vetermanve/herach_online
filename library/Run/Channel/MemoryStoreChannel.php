<?php


namespace Run\Channel;


use Run\ChannelMessage\ChannelMsgProto;

class MemoryStoreChannel extends DataChannelProto
{
    /**
     * @var ChannelMsgProto
     */
    private $message;
    
    /**
     * Подготовка к отправке данных
     *
     * @return mixed
     */
    public function prepare()
    {
        // TODO: Implement prepare() method.
    }
    
    /**
     * Непосредственно отпрвка данных
     *
     * @param $msg
     *
     * @return null
     */
    public function send(ChannelMsgProto $msg)
    {
        $this->message = $msg;
    }
    
    /**
     * @return ChannelMsgProto
     */
    public function getMessage()
    {
        return $this->message;
    }
    
}