<?php


namespace Rpc;


class RpcMaster
{
    /**
     * @var Request[]
     */
    private $requests   = [];
    private $enabled    = false;
    private $serviceRpc = [];
    private $id = 0;
    
    public static function i()
    {
        static $i;
        
        return $i ? $i : $i = new self();
    }
    
    private function getId()
    {
        return str_pad(++$this->id, 3, '0', STR_PAD_LEFT);
    }
    
    public function registerRpc(Request $request)
    {
        $requestId = $this->getId();
        
        if ($this->enabled) {
            $this->requests[$requestId] = $request;
        }
        
        return $requestId;
    }
    
    public function getProfiling()
    {
        if (!$this->enabled) {
            return [1 => 'Profiler disabled'];
        }
        
        $data = [];
        
        foreach ($this->requests as $request) {
            $data[$request->getName()] = $request->getProfiling();
        }
        
        $data += $this->serviceRpc;
        ksort($data);
        return $data;
    }
    
    public function logRpcCall($service, $method, $getTime, $exTime)
    {
        $this->serviceRpc['[' . $this->getId() . '] ' . $service . ':' . $method] = [
                'process' => round($exTime, 4),
                'get' => round($getTime, 4),
            ]
        ;
    }
    
    /**
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }
    
    public function clear()
    {
        $this->requests   = [];
        $this->serviceRpc = [];
        $this->id = 0;
    }
}