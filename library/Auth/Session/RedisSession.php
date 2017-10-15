<?php


namespace Auth\Session;


use Mu\Env;
use Mu\Interfaces\SessionInterface;
use Run\Spec\HttpRequestMetaSpec;
use Run\Util\ChannelState;
use Uuid\Uuid;

/**
 * Сессия хранимая в редисе.
 * 
 * Class RedisSession
 * @package Auth\Session
 */
class RedisSession implements SessionInterface
{
    const SESSION_PREFIX = 'session:';
    const USER_ID        = '_uid';
    
    const SESSION_LIFETIME = 2592000;
    const TOUCH_THROTTLE = 2592000;
    
    private $sid = '';
    
    /**
     * @var ChannelState
     */
    private $channelState;
    
    /**
     * Данные сессии серверной стороны
     * 
     * @var array
     */
    private $sessionData = [];
    
    /**
     * Данные о авторизации, если авторизация не пользователя 
     * а через api
     * 
     * @var []
     */
    private $appInfo = [];
    
    /**
     * Старт сессии
     *
     * @return string
     */
    public function start()
    {
        if ($this->sid) {
            return $this->sid;
        }
        
        $sid = $this->channelState->get(HttpRequestMetaSpec::CHANNEL_SESSION_ID);
        
        if (!$sid) {
            $sid = Uuid::v4();
            $this->channelState->set(HttpRequestMetaSpec::CHANNEL_SESSION_ID, $sid, self::SESSION_LIFETIME);
        } else {
            $this->channelState->touch(HttpRequestMetaSpec::CHANNEL_SESSION_ID);
        }
        
        return $this->sid = $sid;
    }
    
    /**
     * Закрытие сессию
     *
     * @return void
     */
    public function destroy()
    {
        $this->channelState->delete(HttpRequestMetaSpec::CHANNEL_SESSION_ID);
        Env::getRedis()->remove($this->_getStorageKey());
    }
    
    /**
     * @param string $key
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function get($key, $defaultValue = null)
    {
        return $this->_read($key, $defaultValue);
    }
    
    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function set($key, $value)
    {
        return $this->_save($key, $value);
    }
    
    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->_read($key) !== null;
    }
    
    /**
     * @param string $key
     *
     * @return void
     */
    public function remove($key)
    {
        $this->_delete($key);
    }
    
    /**
     * Вернет идентификатор сессии
     *
     * @return string
     */
    public function getSid()
    {
        return $this->sid;
    }
    
    /**
     * Вернет идентификатор пользователя
     *
     * @return int
     */
    public function getUserId()
    {
        return (int)$this->_read(self::USER_ID, 0);
    }
    
    /**
     * Установка идентификатора пользователя
     *
     * @param int $userId
     *
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->_save(self::USER_ID, $userId);
        
        return $this;
    }
    
    /**
     * Проверка, активна ли сессия
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->sid;
    }
    
    private function _getStorageKey()
    {
        if (!$this->sid) {
            $this->start();
        }
        
        return self::SESSION_PREFIX . $this->sid;
    }
    
    private function _delete($key)
    {
        unset($this->sessionData[$key]);
        
        return Env::getRedis()->hdel($this->_getStorageKey(), $key);
    }
    
    private function _save($key, $value)
    {
        $this->sessionData[$key] = $value;
        return Env::getRedis()->hset($this->_getStorageKey(), $key, $value, self::SESSION_LIFETIME);
    }
    
    private function _read($key, $default = null)
    {
        if (!array_key_exists($key, $this->sessionData)) {
            $result = Env::getRedis()->hget($this->_getStorageKey(), $key);
            
            $this->sessionData[$key] = $result !== false ? $result : $default;
        }
        
        return $this->sessionData[$key];
    }
    
    /**
     * @return ChannelState
     */
    public function getChannelState()
    {
        return $this->channelState;
    }
    
    /**
     * @param ChannelState $channelState
     */
    public function setChannelState($channelState)
    {
        $this->channelState = $channelState;
    }
    
    /**
     * Продлить время действия сессии
     *
     * @return mixed
     */
    public function touch()
    {
        Env::getRedis()->expire($this->_getStorageKey(), self::SESSION_LIFETIME);
        $this->channelState->set(HttpRequestMetaSpec::CHANNEL_SESSION_ID, $this->sid, self::SESSION_LIFETIME);
    }
    
    /**
     * @return mixed
     */
    public function getAppInfo()
    {
        return $this->appInfo;
    }
    
    /**
     * @param mixed $appInfo
     */
    public function setAppInfo($appInfo)
    {
        $this->appInfo += (array)$appInfo;
    }
    
    /**
     * Получить значение из данных о авторизаванном подключении
     *
     * @param      $key
     * @param null $default
     *
     * @return mixed
     */
    public function getAppInfoItem($key, $default = null)
    {
        return isset($this->appInfo[$key]) ? $this->appInfo[$key] : $default;
    }
    
    /**
     * @param $key
     * @param $value
     *
     * @return mixed|void
     */
    public function setAppInfoItem($key, $value)
    {
        $this->appInfo[$key] = $value;
    }
    
    /**
     * Вернуть все данные из сессии
     *
     * @return mixed
     */
    public function getData()
    {
        $this->sessionData = Env::getRedis()->hgetall($this->_getStorageKey());
        return $this->sessionData;
    }
}