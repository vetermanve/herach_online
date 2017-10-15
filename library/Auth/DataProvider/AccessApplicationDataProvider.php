<?php


namespace Auth\DataProvider;


use Mu\Env;
use Mu\ServicesTrait;

class AccessApplicationDataProvider extends SessionDataProviderProto
{
    use ServicesTrait;
    
    private $data = [];
    
    private $isDataLoaded = false;
    
    private function loadData () {
        $app = $this->getAuthService()->getAccessApplicationByAppKey($this->token);
    
        if (!empty($app)) {
            $this->data = $app->asArray();
        }
        
        $this->isDataLoaded = true;
    }
    
    /**
     * Закрытие сессию
     *
     * @return void
     */
    public function destroyData()
    {
        // not supported
    }
    
    /**
     * @param string $key
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function get($key, $defaultValue = null)
    {
        !$this->isDataLoaded && $this->loadData();
        
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
        // not supported
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
        // not supported
    }
    
    /**
     * Read all data
     *
     * @return []
     */
    public function getAll()
    {
        !$this->isDataLoaded && $this->loadData();
        return $this->data;
    }
    
    /**
     * @return mixed
     */
    public function updateExpiration()
    {
        // not supported
        return false;
    }
}