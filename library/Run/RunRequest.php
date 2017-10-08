<?php


namespace Run;

use Run\Util\ChannelState;

class RunRequest
{
    /**
     * Массив параметров запроса,
     * в случае http это query
     *
     * @var []
     */
    public $params = [];
    
    /**
     * Массив с телом данных запроса,
     * в случае http это body из post
     *
     * @var []
     */
    public $data = [];
    
    /**
     * Сырые данные в теле запроса
     * 
     * @var string
     */
    public $body = '';
    
    /**
     * Метаданные запроса,
     * в случае HTTP это заголовки
     *
     * @var []
     */
    public $meta = [];
    
    /**
     * Айди тела сообщения
     * обычно это uuid v4
     *
     * @var string
     */
    private $uid;
    
    /**
     * Запрашиваемый ресурс
     * Например auth
     *
     * @var
     */
    private $resource;
    
    /**
     * Обратный адрес для ответа
     *
     * @var string
     */
    private $reply;
    
    /**
     * Метаданные состояния канала
     *
     * @var ChannelState
     */
    private $channelState;
    
    /**
     * RunRequest constructor.
     *
     * @param string $uid
     * @param        $resource
     * @param string $reply
     */
    public function __construct($uid, $resource, $reply = '')
    {
        $this->uid      = $uid;
        $this->resource = $resource;
        $this->reply    = $reply;
        $this->channelState = new ChannelState();
    }
    
    public function getMeta ($key, $default = null) 
    {
        return isset($this->meta[$key]) ? $this->meta[$key] : $default;
    }
    
    public function getMetaItem ($metaKey, $key, $default = null)
    {
        return isset($this->meta[$metaKey][$key]) ? $this->meta[$metaKey][$key] : $default;
    }
    
    public function getParam ($key, $default = null) 
    {
        return isset($this->params[$key]) ? $this->params[$key] : $default;
    }
    
    public function getData ($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }
    
    public function getParamOrData ($key, $default = null) 
    {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }
        
        if ($this->data && isset($this->data[$key])) {
            return $this->data[$key];
        }
        
        return $default;
    }
    
    /**
     * Получить объект состояния канала
     * 
     * @return ChannelState
     */
    public function getChannelState () 
    {
        return $this->channelState;
    }
    
    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }
    
    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }
    
    public function getDesc()
    {
        return $this->resource . ' ' . $this->uid;
    }
    
    /**
     * @return string
     */
    public function getReply()
    {
        return $this->reply;
    }
}