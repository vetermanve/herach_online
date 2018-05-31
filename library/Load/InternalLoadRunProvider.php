<?php


namespace Load;


use Run\RunRequest;
use Run\Provider\RunProviderProto;

class InternalLoadRunProvider extends RunProviderProto
{
    /**
     * @var Load[]
     */
    protected $loads = [];
    
    public function prepare()
    {
    }
    
    public function run()
    {
        while ($this->loads) {
            $load = array_pop($this->loads);
            $this->_processLoad($load);
        }
    }
    
    private function _processLoad(Load $load) {
        // собираем реквест
        $request = new RunRequest($load->getUuid(), $load->getResource());
        $request->params = $load->getParams();
    
        // отдаем в работу
        $this->core->process($request);
    }
    
    /**
     * @param Load[] $loads
     */
    public function setLoads(array $loads)
    {
        $this->loads = $loads;
    }
}