<?php


namespace Mu\Interfaces;


interface DescribableInterface
{
    /**
     * Получить название описывающее объект
     * 
     * @return string
     */
    public function getName ();
    
    /**
     * Получить массив описания объекта
     * 
     * @return array
     */
    public function getDescription ();   
}