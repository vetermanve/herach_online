<?php


namespace Auth\DataProvider;


class MemoryDataProvider extends SessionDataProviderProto
{
    
    protected $data = [];
    
    /**
     * Закрытие сессию
     *
     * @return void
     */
    public function destroyData()
    {
        $this->data = [];
    }
    
    /**
     * @param string $key
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function get($key, $defaultValue = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $defaultValue;
    }
    
    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function set($key, $value)
    {
        return $this->data[$key] = $value;
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
        unset($this->data[$key]);
    }
    
    /**
     * Read all data
     *
     * @return []
     */
    public function getAll()
    {
        return $this->data;
    }
    
    /**
     * @return mixed
     */
    public function updateExpiration()
    {
        // not supported
    }
    
    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}