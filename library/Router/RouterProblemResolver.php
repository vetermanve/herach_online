<?php


namespace Router;


use iConto\Env;

class RouterProblemResolver
{
    private $problem;
    
    private $reports;
    
    /**
     * RouterProblemResolver constructor.
     *
     * @param $problem
     */
    public function __construct($problem)
    {
        $this->problem = $problem;
    }
    
    public function addReport ($moduleId, $report) 
    {
        if (isset($this->reports[$moduleId])) {
            return false;
        }
        
        $this->reports[$moduleId] = $report;
    
        Env::getLogger()->addError(__CLASS__.' "'.$report.'" problem on '.$moduleId, [
            'server' => get_object_vars($this),
        ]);
        
        return true;
    }
}