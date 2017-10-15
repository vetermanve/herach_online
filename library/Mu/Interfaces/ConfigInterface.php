<?php


namespace Mu\Interfaces;


interface ConfigInterface
{
    /**
     * Возвращает одно занчение
     *
     * @param string $key
     * @param string $section
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get($key, $section = null, $defaultValue = null);
    
    /**
     * Возвращает полный массив настроек
     * 
     * @return array
     */
    public function toArray();
    
    /**
     * Получить целиком секцию
     * 
     * @param string $section
     *
     * @return array
     */
    public function getSection($section);
}