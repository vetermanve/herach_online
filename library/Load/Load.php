<?php

namespace Load;

class Load
{
    private $target;
    
    private $count;
    
    private $bookmark;
    
    private $results = [];
    
    /**
     * Load constructor.
     *
     * @param $target
     */
    public function __construct($target = null)
    {
        $this->target = $target;
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
}