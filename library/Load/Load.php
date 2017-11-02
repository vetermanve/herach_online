<?php

namespace Load;

use Uuid\Uuid;

class Load
{
    private $resource;
    
    private $count;
    
    private $bookmark;
    
    private $results = [];
    
    private $uuid;
    
    /**
     * Load constructor.
     *
     * @param $resource
     */
    public function __construct($resource = null)
    {
        $this->resource = $resource;
        $this->uuid = (string)Uuid::v4(); 
    }
    
    /**
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
    }
    
    /**
     * @param array $results
     */
    public function setResults(array $results)
    {
        $this->results = $results;
    }
    
    /**
     * @return null
     */
    public function getResource()
    {
        return $this->resource;
    }
    
    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }
}