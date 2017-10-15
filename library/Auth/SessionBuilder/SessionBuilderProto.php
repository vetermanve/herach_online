<?php


namespace Auth\SessionBuilder;


use Mu\Interfaces\SessionInterface;

abstract class SessionBuilderProto
{
    abstract public function prepare();
    abstract public function buildSession(SessionInterface $session, $token, $resource);
}