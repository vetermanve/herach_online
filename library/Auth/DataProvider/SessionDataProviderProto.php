<?php


namespace Auth\DataProvider;


abstract class SessionDataProviderProto
{
    /**
     * @var string
     */
    protected $token;
    
    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }
    
    /**
     * Закрытие сессию
     *
     * @return void
     */
    abstract public function destroyData();
    
    /**
     * @param string $key
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    abstract public function get($key, $defaultValue = null);
    
    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    abstract public function set($key, $value);
    
    /**
     * @param string $key
     *
     * @return bool
     */
    abstract public function has($key);
    
    /**
     * @param string $key
     *
     * @return void
     */
    abstract public function remove($key);
    
    /**
     * Read all data
     *
     * @return []
     */
    abstract public function getAll();
    
    /**
     * @return mixed
     */
    abstract public function updateExpiration();
}