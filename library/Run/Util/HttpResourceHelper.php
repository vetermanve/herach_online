<?php


namespace Run\Util;


class HttpResourceHelper
{
    const R_1_PART_RESOURCE = 0;
    const R_1_PART_ITEM_ID  = 1;
    
    const R_3_PART_TYPE     = 0;
    const R_3_PART_VERSION  = 1;
    const R_3_PART_RESOURCE = 2;
    
    const R_3_PART_ITEM_ID = 3;
    
    const TYPE_REST  = 'rest';
    const TYPE_OAUTH = 'oauth';
    
    private $string   = '';
    private $resource = '';
    private $version  = 1;
    private $type;
    
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
        
        if (count($data) === 1) { // /auth
            $this->type     = self::TYPE_REST;
            $this->resource = $data[self::R_1_PART_RESOURCE];
        } elseif (count($data) === 2) {  // /user/644
            $this->type     = self::TYPE_REST;
            $this->resource = $data[self::R_1_PART_RESOURCE];
            $this->id       = $data[self::R_1_PART_ITEM_ID];
        } elseif (count($data) > 2) { // /rest/2.0/auth
            $this->type     = (string)$data[self::R_3_PART_TYPE];
            $this->version  = (int)$data[self::R_3_PART_VERSION];
            $this->resource = (string)$data[self::R_3_PART_RESOURCE];
            
            if ($this->type === self::TYPE_OAUTH) { // /oauth/2.0/auth
                $this->resource = 'oauth2-' . $this->resource;
            }
        }
        
        if (count($data) == 4) { // /rest/2.0/user/644
            $this->id = $data[self::R_3_PART_ITEM_ID];
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
}