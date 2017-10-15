<?php


namespace Run\Rest;


use iConto\Dispatcher\Encoder\IEncoder;
use iConto\Interfaces\DispatcherInterface;
use Run\Rest\Exception\Redirect;
use Run\RunRequest;
use Run\Spec\HttpRequestMetaSpec;

class RestRequestOptions 
{
    /**
     * @var RunRequest;
     */
    private $request;
    
    private $allParams;
    
    /**
     * Это фарш отсюда нужно сносить
     * всместе с вышестоящим энкодером
     * 
     * @var mixed
     */
    private $decodedBody;
    
    
    /**
     * Добавлено для обратной совместимости
     * Ничего не меняет
     * @todo убрать, работа с кодировками должна быть на уровне процессора
     * 
     * @var string;
     */
    private $contentType;
    
    /**
     * @return string|null
     */
    public function getUserAgent()
    {
        return $this->request->getMeta(HttpRequestMetaSpec::CLIENT_AGENT, 'unknown');
    }
    
    public function getUserAgentType()
    {
        return $this->request->getMeta(HttpRequestMetaSpec::CLIENT_TYPE, 'unknown');
    }
    
    /**
     * @return string|null
     */
    public function getOrigin()
    {
        return $this->request->getMeta(HttpRequestMetaSpec::REQUEST_SOURCE);
    }
    
    /**
     * Получение IP адреса с которого пришел запрос
     * 
     * @return string|null
     */
    public function getClientIp()
    {
        return $this->getHeader('x-forwarded-for') ?: '000.000.000.000';
    }
    
    public function getUrl()
    {
        return $this->request->getResource();
    }
    
    /**
     * @return array
     */
    public function getParams()
    {
        return $this->allParams;
    }
    
    /**
     *
     * @param      $key
     * @param null $default
     *
     * @return mixed
     */
    public function getParam($key, $default = null)
    {
        if (isset($this->allParams[$key])) {
            return $this->allParams[$key];
        }
        
        return $default;
    }
    
    /**
     * @param string $contentType
     *
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->getParam('id');
    }
    
    /**
     * @return int[]
     */
    public function getIds()
    {
        return $this->getParam('ids');
    }
    
    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->request->getMeta(HttpRequestMetaSpec::REQUEST_METHOD);
    }
    
    /**
     * @return string
     */
    public function getResource()
    {
        return $this->request->getResource();
    }
    
    /**
     * @return array
     */
    public function getFilters()
    {
        return [];
    }
    
    /**
     * @return string
     */
    public function getBody()
    {
        return $this->request->body;
    }
    
    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->request->getMeta(HttpRequestMetaSpec::CLIENT_LOCALE);
    }
    
    /**
     * Получение всех параметров запроса
     * Фильтрация растпространяется на параметры, получаемые этим методом
     * @return array
     */
    public function getRequestParams()
    {
        return $this->allParams;
    }
    
    public function setRequestParams($arrayParams)
    {
        $this->allParams = $arrayParams + $this->allParams;
    }
    
    /**
     * @return string
     */
    public function getReqiestId()
    {
        return $this->request->getUid();
    }
    
    public function redirect($url)
    {
        $redirect = new Redirect();
        $redirect->setUrl($url);
        throw $redirect;
    }
    
    /**
     * @param RunRequest $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
        $this->allParams = $this->request->params + $this->request->data;
    }
    
    /* Всякая фигня с декодированием */
    
    /**
     * @param null $decodedBody
     *
     * @return $this
     */
    public function setDecodedBody($decodedBody)
    {
        $this->decodedBody = $decodedBody;
    
        return $this;
    }
    
    /**
     * @return null
     */
    public function getDecodedBody()
    {
        return $this->decodedBody !== null ? $this->decodedBody : $this->request->data;
    }
    
    public function getHeader($name)
    {
        return $this->request->getMetaItem(HttpRequestMetaSpec::REQUEST_HEADERS, strtolower($name));
    }
    
    public function getParamsByKeys($paramsKeys)
    {
        if (!$paramsKeys) {
            return $this->allParams; 
        }
        
        $result = [];
        
        foreach ($paramsKeys as $key) {
            if (isset($this->allParams[$key])) {
                $result[$key] = $this->allParams[$key];
            }
        }
        
        return $result;
    }
    
    /**
     * Получение платформы c которой отправлен запрос
     *
     * @return string|null
     */
    public function getPlatform()
    {
        return $this->getHeader('x-rest-app'); 
    }
    
    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }
}