<?php


namespace Run\Module\Session;namespace Auth\Session;

use Mu\Interfaces\SessionInterface;
use yii\console\Exception;

/**
 * Не хранимая локальная сессия
 * 
 * Class LocalSession
 * @package Auth\Session
 */
class LocalSession implements SessionInterface
{
 
    private $sessionData = [];
    
    /**
     * Старт сессии
     *
     * @return string
     */
    public function start()
    {
        
    }
    
    /**
     * Закрытие сессию
     *
     * @return void
     */
    public function destroy()
    {
        
    }
    
    /**
     * @param string $key
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function get($key, $defaultValue = null)
    {
        return isset($this->sessionData[$key]) ? $this->sessionData[$key] : $defaultValue;   
    }
    
    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function set($key, $value)
    {
        $this->sessionData[$key] = $value;
    }
    
    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return isset($this->sessionData[$key]);
    }
    
    /**
     * @param string $key
     *
     * @return void
     */
    public function remove($key)
    {
        unset($this->sessionData[$key]);
    }
    
    /**
     * Вернет идентификатор сессии
     *
     * @return string
     */
    public function getSid()
    {
        return 'no_sid';
    }
    
    /**
     * Вернет идентификатор пользователя
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->get('user_id', 0);
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
        $this->set('user_id', $userId);
        
        return $this;
    }
    
    /**
     * Проверка, активна ли сессия
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->sessionData;
    }
    
    /**
     * Продлить время действия сессии
     *
     * @return mixed
     */
    public function touch()
    {
        
    }
    
    /**
     * Установка данных о авторизаванном подключении
     *
     * @param $appInfo
     * 
     * @return []
     */
    public function setAppInfo($appInfo)
    {
        return $this->sessionData += (array)$appInfo;
    }
    
    /**
     * Массив данных о авторизаванном подключении
     *
     * @return []
     */
    public function getAppInfo()
    {
        return $this->sessionData;
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
        $this->get($key, $default);
    }
    
    /**
     * Установить значение для ключа данных подключения
     *
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function setAppInfoItem($key, $value)
    {
        return $this->set($key, $value);
    }
    
    /**
     * Вернуть все данные из сессии
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->sessionData;
    }
}