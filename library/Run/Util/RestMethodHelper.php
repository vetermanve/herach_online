<?php


namespace Run\Util;


use Run\RunRequest;

class RestMethodHelper
{
    const METHOD_OVERRIDE_KEY = '_method';
    
    const ANY_METHOD = '*';
    
    const METHOD_GET     = 'get';
    const METHOD_VIEW    = 'view';
    const METHOD_PUT     = 'put';
    const METHOD_POST    = 'post';
    const METHOD_DELETE  = 'delete';
    const METHOD_OPTIONS = 'options';
    
    /**
     * Явно разрешенные переназначения методов
     *
     * @var array
     */
    protected static $methodOverrideDeny = [
        self::METHOD_GET     => [
            self::METHOD_POST   => 1,
            self::METHOD_PUT    => 1,
            self::METHOD_DELETE => 1,
        ],
        self::METHOD_VIEW    => [
            self::METHOD_POST   => 1,
            self::METHOD_PUT    => 1,
            self::METHOD_DELETE => 1,
        ],
        self::METHOD_OPTIONS => [
            self::ANY_METHOD => 1,
        ],
        self::METHOD_DELETE  => [
            self::ANY_METHOD => 1,
        ],
    ];
    
    
    public static function getRealMethod($requestMethod, RunRequest $request)
    {
        $requestMethod = strtolower($requestMethod);
        
        $methodOverride = (string)$request->getParamOrData(self::METHOD_OVERRIDE_KEY);
        
        unset($request->data[self::METHOD_OVERRIDE_KEY], $request->params[self::METHOD_OVERRIDE_KEY]);
        
        if ($requestMethod === 'get' && $request->getParam('id')) {
            $requestMethod = 'view';
        }
        
        if (!$methodOverride
            || isset(self::$methodOverrideDeny[$requestMethod][self::ANY_METHOD])
            || isset(self::$methodOverrideDeny[$requestMethod][$methodOverride])
        ) {
            return $requestMethod;
        }
        
        return $methodOverride;
    }
    
    public static function makeStrictParams(&$params)
    {
        foreach ($params as &$value) {
            if (is_array($value)) {
                self::makeStrictParams($value);
            } elseif (is_numeric($value) && (string)(int)$value === $value) {
                $value = (int)$value;
            }
        }
        
        return $params;
    }
}
