<?php


namespace Modular;


use Exception;

class ModularContextProto
{
    /**
     * Контейнер данных
     * 
     * @var array
     */
    protected $data = [];
    
    /**
     * Конфигурирование среды
     * 
     * @var array
     */
    protected $env = [];
    
    /**
     * Версия данных для инвалидации хэша
     *
     * @var int
     */
    protected $version = 0;
    
    /**
     * Данные хэша контекста
     *
     * @var array
     */
    protected $contextHashData = [
        'version' => null,
        'hash' => '',
    ];
    
    
    /**
     * Инициировать (заполнить) конекст данными.
     * Можно делать только один раз.
     * 
     * @param $data
     *
     * @throws Exception
     */
    public function fill($data)
    {
        if ($this->data) {
            throw new Exception('Trying to feel not empty context');
        }
        
        $this->data = $data;
        $this->version++;
    }
    
    
    /**
     * Задать одно значение в контексте
     * 
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
        $this->version++;
        
        return $this;
    }
    
    /**
     * Получить значение из контекста
     * 
     * @param      $key
     * @param null $default
     *
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }
    
    /**
     * Получить ссылку на переменную в массиве конфигурации
     * 
     * @param $key
     * @param $writeDefault
     *
     * @return mixed
     */
    public function &getLink ($key, $writeDefault)
    {
        if (!isset($this->data[$key])) {
            $this->data[$key] = $writeDefault;
        }
        
        return $this->data[$key];
    }
    
    /**
     * Проверить что значение задано и не пустое
     * 
     * @param $key
     *
     * @return bool
     */
    public function is ($key)
    {
        return isset($this->data[$key]) && $this->data[$key];
    }
    
    /**
     * Задать значение ключу в массиве
     * 
     * @param $scope
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setScope($scope, $key, $value)
    {
        $this->version++;
        $this->data[$scope][$key] = $value;
        return $this;
    }
    
    /**
     * Получить значение ключа в массиве
     * 
     * @param      $scope
     * @param      $key
     * @param null $default
     *
     * @return mixed
     */
    public function getScope($scope, $key, $default = null)
    {
        return isset($this->data[$scope][$key]) ? $this->data[$scope][$key] : $default;
    }
    
    /**
     * Булевая проверка значения в ключе
     *
     * @param      $scope
     * @param      $key
     * 
     * @return bool
     */
    public function isScope($scope, $key)
    {
        return isset($this->data[$scope][$key]) && $this->data[$scope][$key];
    }
    
    /**
     * Уставновить управляющее значение
     * которое не должно влиять на чексумму контекста
     * 
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function setEnv ($key, $value) 
    {
        $this->env[$key] = $value;
        return $this;
    }
    
    /**
     * Получить управляющее значение, по умлолчани = false
     * 
     * @param      $key
     * @param bool $default
     *
     * @return bool|mixed
     */
    public function getEnv ($key, $default = false) 
    {
        return isset($this->env[$key]) ? $this->env[$key] : $default; 
    }
    
    /**
     * Получить все данные контекста
     * 
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Получить хэш текущего состояния данных.
     * 
     * @return mixed
     */
    public function getHash ()
    {
        if ($this->contextHashData['version'] !== $this->version) {
            $this->contextHashData['version'] = $this->version;
            $this->contextHashData['hash'] = md5(json_encode($this->data));
        }
        
        return $this->contextHashData['hash'];
    }
}