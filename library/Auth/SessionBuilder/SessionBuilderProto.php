<?php


namespace Auth\SessionBuilder;


use iConto\Interfaces\SessionInterface;

abstract class SessionBuilderProto
{
    abstract public function prepare();
    abstract public function buildSession(SessionInterface $session, $token, $resource);
}