<?php


namespace Auth\Session;


use Auth\AuthConfig;
use Auth\DataProvider\SessionDataProviderProto;
use Mu\Interfaces\SessionInterface;

class AnySession implements SessionInterface
{
    /**
     * @var SessionDataProviderProto
     */
    private $dataProvider;
    
    /**
     * Старт сессии
     *
     * @return string
     */
    public function start()
    {
        // TODO: Implement start() method.
    }
    
    /**
     * Закрытие сессию
     *
     * @return void
     */
    public function destroy()
    {
        $this->dataProvider->destroyData();
    }
    
    /**
     * @param string $key
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function get($key, $defaultValue = null)
    {
        return $this->dataProvider->get($key, $defaultValue);
    }
    
    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function set($key, $value)
    {
        return $this->dataProvider->set($key, $value);
    }
    
    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->dataProvider->has($key);
    }
    
    /**
     * @param string $key
     *
     * @return void
     */
    public function remove($key)
    {
        $this->dataProvider->remove($key);
    }
    
    /**
     * Вернет идентификатор сессии
     *
     * @return string
     */
    public function getSid()
    {
        return $this->dataProvider->getToken();
    }
    
    /**
     * Вернет идентификатор пользователя
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->get(AuthConfig::FIELD_USER_ID, 0);
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
        return $this->set(AuthConfig::FIELD_USER_ID, $userId);
    }
    
    /**
     * Продлить время действия сессии
     *
     * @return mixed
     */
    public function touch()
    {
        $this->dataProvider->updateExpiration();
    }
    
    /**
     * Установка данных о авторизаванном подключении
     *
     * @param $appInfo
     *
     * @return  []
     */
    public function setAppInfo($appInfo)
    {
        // TODO: Implement setAppInfo() method.
    }
    
    /**
     * Массив данных о авторизаванном подключении
     *
     * @return []
     */
    public function getAppInfo()
    {
        return $this->dataProvider->getAll();
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
        return $this->get($key, $default);
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
     * @return SessionDataProviderProto
     */
    public function getDataProvider()
    {
        return $this->dataProvider;
    }
    
    /**
     * @param SessionDataProviderProto $dataProvider
     */
    public function setDataProvider($dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }
    
    /**
     * Вернуть все данные из сессии
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->dataProvider->getAll();
    }
}