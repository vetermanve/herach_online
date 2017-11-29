<?php


namespace Run\Util;


class HttpResourceHelper
{
    const R_1_PART_RESOURCE = 0;
    const R_1_PART_ITEM_ID  = 1;
    const R_1_PART_METHOD   = 2;
    
    const R_3_PART_TYPE     = 0;
    const R_3_PART_VERSION  = 1;
    const R_3_PART_RESOURCE = 2;
    
    const R_3_PART_ITEM_ID = 3;
    
    const TYPE_REST  = 'rest';
    const TYPE_WEB   = 'web';
    const TYPE_OAUTH = 'oauth';
    
    private $string   = '';
    private $resource = '';
    private $version  = 1;
    private $type;
    private $method;
    
    
    private $id;
    
    /**
     * HttpResourceHelper constructor.
     *
     * @param     $string
     * @param int $defaultVersion
     */
    public function __construct($string, $defaultVersion = 2)
    {
        $this->string  = $string;
        $this->version = $defaultVersion;
        $this->_parse();
    }
    
    private function _parse()
    {
        $path = strpos($this->string, '?') ? strstr($this->string, '?', true) : $this->string;
        $data = explode('/', trim($path, '/'));
        
        if (count($data) > 1 && $data[0] === 'rest') {
            array_shift($data);
            $this->type     = self::TYPE_REST;
        } else {
            $this->type     = self::TYPE_WEB;
        }
        
        if (count($data) === 1) { // /auth
            $this->resource = $data[self::R_1_PART_RESOURCE];
        } elseif (count($data) === 2) {  // /user/644
            $this->resource = $data[self::R_1_PART_RESOURCE];
            $this->id       = $data[self::R_1_PART_ITEM_ID];
        } elseif (count($data) > 2) { // /user/644/edit //web specific 
            $this->id       = $data[self::R_1_PART_ITEM_ID];
            $this->resource = $data[self::R_1_PART_RESOURCE];
            $this->method   = $data[self::R_1_PART_METHOD];
        }
    }
    
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }
    
    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
    
    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }
}