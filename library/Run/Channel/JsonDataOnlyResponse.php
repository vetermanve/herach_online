<?php


namespace Run\Channel;


use Run\ChannelMessage\ChannelMsgProto;
use Run\RunConfig;
use Run\RunContext;

class JsonDataOnlyResponse extends JsonHttpResponseChannel
{
    protected function _writeBody(ChannelMsgProto $msg)
    {
        if (isset($msg->body['data'])) {
            echo is_string($msg->body['data']) ? $msg->body['data'] : json_encode($msg->body['data'], JSON_UNESCAPED_UNICODE);
        }
        
        if ($this->context->getEnv(RunContext::ENV_DEBUG)) {
            $this->runtime->debug(__METHOD__.' -> '.$msg->body); 
        }
    }
    
}