<?php


namespace Run\Spec;


class HttpResponseSpec
{
    const STATUS_ERROR = 1;
    const STATUS_OK    = 0;
    
    const HTTP_CODE_OK = 200;
    
    const HTTP_CODE_REDIRECT = 302;
    
    const HTTP_CODE_BAD_REQUEST = 400;
    const HTTP_CODE_UNAUTHORIZED= 401;
    const HTTP_CODE_NOT_FOUND   = 404;
    const HTTP_CODE_UNSUPPORTED = 406;
    const HTTP_CODE_CONFLICT    = 409;
    
    const HTTP_CODE_ERROR = 500;
    
    const META_HTTP_HEADERS = 'headers';
    const META_HTTP_CODE    = 'code';
    const META_EXECUTION_TIME = 'ex_time';
    
    const META_HTTP_HEADER_REQUEST_ID  = 'X-Request-Id';
    const META_HTTP_HEADER_STATUS_CODE = 'X-Status-Code';
    const META_HTTP_HEADER_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
    const META_HTTP_HEADER_LOCATION = 'Location';
    const META_HTTP_HEADER_EXECUTION_TIME  = 'X-Time';
    
    const MESSAGE    = 'msg';
    const STATUS     = 'status';
    const REQUEST_ID = 'request_id';
    const TIME       = 'time';
    const DATA       = 'data';
    const USER_ID    = 'user_id';
    
    public static $absoluteHeaders = [
        'Access-Control-Allow-Headers'     => 'Origin, Accept, X-Suppress-HTTP-Code, Content-Type, X-Rest-App, Authorization',
        'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, DELETE, HEAD, PUT, COUNT',
        'Access-Control-Expose-Headers'    => 'X-Status-Code, Content-Type, X-Application',
        'Access-Control-Allow-Credentials' => 'true',
        'Content-Type'                     => 'application/json; charset=UTF-8',
        'Cache-Control'                    => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
        'Pragma'                           => 'no-cache',
        'Expires'                          => '0',
    ];
}