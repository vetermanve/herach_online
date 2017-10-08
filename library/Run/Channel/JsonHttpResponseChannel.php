<?php


namespace Run\Channel;


use Run\ChannelMessage\ChannelMsgProto;
use Run\RunContext;
use Run\Spec\HttpResponseSpec;

class JsonHttpResponseChannel extends DataChannelProto
{
    /**
     * Подготовка к отправке данных
     *
     * @return mixed
     */
    public function prepare()
    {
        
    }
    
    /**
     * Непосредственно отпрвка данных
     *
     * @param $msg
     *
     * @return mixed
     */
    public function send(ChannelMsgProto $msg)
    {
        if (function_exists('http_response_code')) {
            http_response_code($msg->getCode() ?: HttpResponseSpec::HTTP_CODE_OK);
        }
        
        foreach ($msg->getMeta(HttpResponseSpec::META_HTTP_HEADERS, []) as $header => $value) {
            header($header.':'.$value);
        }
        
        $channelState = $msg->getChannelState();
        $expires = $channelState->getExpiresAt();
        
        $mainHost = $this->context->get(RunContext::HOST);
        $mainHost = explode(':', $mainHost)[0];
    
        $hosts = [];
        if (strpos($mainHost, '.')) {
            $parts = explode('.', $mainHost);
            while (count($parts) > 1) {
                $hosts[] =  '.'.implode('.', $parts);
                array_shift($parts);
            }
        } else {
            $hosts[] = $mainHost;
        }
        
        $isSecure = $this->getContext()->get(RunContext::IS_SECURE_CONNECTION);
        
        foreach ($channelState->pack() as $key => $data) {
            foreach ($hosts as $host) {
                setcookie($key,  $data, $expires[$key], '/', $host, $isSecure, true);
            }
        }
        
        $this->_writeBody($msg);
        $this->runtime->debug('REST_RAW_RESPONSE', $msg->getDebugInfo());
    }
    
    protected function _writeBody(ChannelMsgProto $msg) {
        echo is_string($msg->body) ? $msg->body : json_encode($msg->body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);   
    }
}