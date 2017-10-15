<?php


namespace Mu\Interfaces;


interface SessionInterface
{
    /**
     * Старт сессии
     *
     * @return string
     */
    public function start();
    
    /**
     * Закрытие сессию
     *
     * @return void
     */
    public function destroy();
    
    /**
     * @param string $key
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    public function get($key, $defaultValue = null);
    
    /**
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function set($key, $value);
    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key);
    
    /**
     * @param string $key
     *
     * @return void
     */
    public function remove($key);
    
    /**
     * Вернет идентификатор сессии
     *
     * @return string
     */
    public function getSid();
    
    /**
     * Вернет идентификатор пользователя
     *
     * @return int
     */
    public function getUserId();
    
    /**
     * Установка идентификатора пользователя
     *
     * @param int $userId
     *
     * @return $this
     */
    public function setUserId($userId);
    
    /**
     * Продлить время действия сессии
     * 
     * @return mixed
     */
    public function touch ();
    
    /**
     * Установка данных о авторизаванном подключении
     *
     * @param $appInfo
     *
     * @return  []
     */
    public function setAppInfo ($appInfo);
    
    /**
     * Массив данных о авторизаванном подключении
     * 
     * @return []
     */
    public function getAppInfo ();
    
    /**
     * Получить значение из данных о авторизаванном подключении
     *
     * @param      $key
     * @param null $default
     *
     * @return mixed
     */
    public function getAppInfoItem ($key, $default = null);
    
    /**
     * Установить значение для ключа данных подключения
     * 
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function setAppInfoItem($key, $value);
    
    /**
     * Вернуть все данные из сессии
     * 
     * @return mixed
     */
    public function getData (); 
}