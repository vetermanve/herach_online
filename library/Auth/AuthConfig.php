<?php


namespace Auth;


class AuthConfig
{
    const FIELD_USER_ID = '_uid';
    
    const AUTH_API_AUTHORIZED    = 'api_authorized';
    const AUTH_API_UNAUTHORIZED  = 'api_unauthorized';
    const AUTH_API_INVALID_AUTH  = 'api_invalid_auth'; // expired and etc
    const AUTH_API_FORBIDDEN     = 'api_forbidden';
    
    const AUTH_USER_AUTHORIZED   = 'user_authorized';
    const AUTH_USER_UNAUTHORIZED = 'user_unauthorized';
    
    const AUTH_TOTAL_UNAUTHORIZED = 'user_new_session';
    
    const HIGHEST_PRIORITY = 1;
    
    public static $authorizationPriority = [
        /* some authorized */
        self::AUTH_USER_AUTHORIZED    => self::HIGHEST_PRIORITY,
        self::AUTH_API_AUTHORIZED     => 2,
    
        /* some unauthorized */
        self::AUTH_USER_UNAUTHORIZED  => 3,
        self::AUTH_API_UNAUTHORIZED   => 4,
        self::AUTH_API_UNAUTHORIZED   => 4,
    
        /* authorization unknown */
        self::AUTH_TOTAL_UNAUTHORIZED => 100,
    ];
}