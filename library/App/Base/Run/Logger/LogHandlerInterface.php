<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 29.04.2018
 * Time: 22:29
 */

namespace App\Base\Run\Logger;


use Monolog\Handler\HandlerInterface;

interface LogHandlerInterface extends HandlerInterface
{
    public function flushLogs($key);
}