<?php


namespace Run\Module;


use iConto\Exception\InternalError;
use iConto\Interfaces\RpcServicesContainerInterface;
use iConto\Service\Service;
use Run\RunContext;
use Run\RunModuleProto;
use Run\Module\RpcServices;

class ServicesModule extends RunModuleProto implements RpcServicesContainerInterface
{
    /**
     * @var RpcServices
     */
    private $servicesConfig;
    
    private $services;
    
    public function init () 
    {
        $this->servicesConfig = new RpcServices();
        $this->servicesConfig->follow($this);
    }
    
    /**
     * @param string $serviceName
     *
     * @return Service
     * 
     * @throws InternalError
     */
    public function getService($serviceName)
    {
        if (isset($this->services[$serviceName])) {
            return $this->services[$serviceName];
        }
        
        $serviceConfig = $this->servicesConfig->getServiceConfig($serviceName);
        
        if (!$serviceConfig) {
            throw new InternalError("There is no " . $serviceName . " service.");
        }
        
        return $this->services[$serviceName] = Service::factory($serviceConfig);
    }
    
    /**
     * @param string $serviceName
     * @param object $object
     *
     * @return void
     */
    public function addService($serviceName, $object)
    {
        $this->services[$serviceName] = $object;
    }
}