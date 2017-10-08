<?php


namespace Auth\DataProvider;


use iConto\Env;

class RedisSessionDataProvider extends SessionDataProviderProto
{
    const SESSION_PREFIX = 'session:';
    const SESSION_LIFETIME = 2592000;
    
    private $redis;
    
    /**
     * Локальный кэш данных
     * 
     * @var [];
     */
    private $dataCache;
    
    /**
     * @var string
     */
    private $storageKey = '';
    
    public function setToken($token)
    {
        parent::setToken($token);
        $this->storageKey = self::SESSION_PREFIX . $this->token; 
    }
    
    /**
     * @return \iConto\Cache\Redis
     */
    private function getRedis() {
        !$this->redis && $this->redis = Env::getRedis();
        return $this->redis;
    }
    
    /**
     * Закрытие сессию
     *
     * @return void
     */
    public function destroyData()
    {
        $this->dataCache = [];
        $this->getRedis()->remove($this->storageKey);
    }
    
    /**
     * @param string $key
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function get($key, $defaultValue = null)
    {
        if (!array_key_exists($key, $this->dataCache)) {
            $result = Env::getRedis()->hget($this->storageKey, $key);
    
            $this->dataCache[$key] = $result !== false ? $result : $defaultValue;
        }
    
        return $this->dataCache[$key];
    }
    
    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function set($key, $value)
    {
        $this->dataCache[$key] = $value;
        return $this->getRedis()->hset($this->storageKey, $key, $value, self::SESSION_LIFETIME);
    }
    
    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->get($key) !== null;
    }
    
    /**
     * @param string $key
     *
     * @return void
     */
    public function remove($key)
    {
        unset($this->dataCache[$key]);
    
        $this->getRedis()->hdel($this->storageKey, $key);
    }
    
    /**
     * Read all data
     * @return array|bool|int|string []
     */
    public function getAll()
    {
        $data = $this->getRedis()->hgetall($this->storageKey);
        
        if (is_array($data)) {
            $this->dataCache = $data;
        }
        
        return $this->dataCache;
    }
    
    /**
     * @return mixed
     */
    public function updateExpiration()
    {
        $this->getRedis()->expire($this->storageKey, self::SESSION_LIFETIME);
    }
}