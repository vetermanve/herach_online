<?php


namespace Auth;


use Modular\ModularContextProto;

class AuthContext extends ModularContextProto
{
    const SESSION_ID = 'session_id';
    const AUTH_TOKEN = 'token';
    
    const RESOURCE = 'resource';
}