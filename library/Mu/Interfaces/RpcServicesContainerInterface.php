<?php


namespace Mu\Interfaces;


use Mu\Service\Service;

interface RpcServicesContainerInterface
{
    /**
     * @param string $serviceName
     *
     * @return Service
     */
    public function getService($serviceName);
    
    /**
     * @param string $serviceName
     * @param object $object
     * 
     * @return void
     */
    public function addService($serviceName, $object);
    
    /**
     * @param string $serviceName
     *
     * @return array|null
     */
    public function getServiceConfig ($serviceName); 
}