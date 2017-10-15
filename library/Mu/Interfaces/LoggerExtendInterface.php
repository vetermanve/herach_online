<?php


namespace Mu\Interfaces;


interface LoggerExtendInterface
{
    /**
     * Получить имя логгера
     *
     * @return string
     */
    public function getLoggerName();
    
    /**
     * Задать имя логгера
     *
     * @param string $loggerName
     */
    public function setLoggerName($loggerName);
    
    /**
     * Заморозка параметра в контексте
     *
     * @param string $param
     * @param mixed $value
     */
    public function freeze($param, $value);
    
    /**
     * Разморозка параметра в контексте
     *
     * @param string $param
     *
     * @return void
     */
    public function unfreeze($param);
    
    /**
     * Добавление записи в лог
     * 
     * @inheritdoc
     */
    public function addRecord($level, $message, array $context = array());
    
    /**
     * Получить параметр из фриза логгера 
     * 
     * @param      $key
     * @param null $default
     *
     * @return mixed
     */
    public function getFromContext ($key, $default = null);
}