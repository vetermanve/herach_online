<?php


namespace Mu\Interfaces;


interface ContainerInterface
{
    public function bootstrap ($module, $required = true);
    public function setModule ($moduleName, $module);
}