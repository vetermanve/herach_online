<?php


namespace Modular;


class ModularProcessor implements ModularSystemModule
{
    const SECTION_VERY_FIRST = 1;
    const SECTION_BEFORE     = 2;
    const SECTION_RUN        = 3;
    const SECTION_AFTER      = 4;
    const SECTION_VERY_LAST  = 5;
    
    const RUN_SERIAL   = 'serial';
    const RUN_PARALLEL = 'parallel';
    
    /**
     * Порядок выполнения стратегий
     *
     * @var array
     */
    private static $sectionsOrder = [
        self::SECTION_VERY_FIRST,
        self::SECTION_BEFORE,
        self::SECTION_RUN,
        self::SECTION_AFTER,
        self::SECTION_VERY_LAST
    ];
    
    /**
     * @var array
     */
    private $sectionsRun = [
        self::SECTION_VERY_FIRST => self::RUN_SERIAL,
        self::SECTION_BEFORE     => self::RUN_SERIAL,
        self::SECTION_RUN        => self::RUN_SERIAL,
        self::SECTION_AFTER      => self::RUN_SERIAL,
        self::SECTION_VERY_LAST  => self::RUN_SERIAL
    ];
    
    /**
     * @var ModularStrategyInterface[][]
     */
    protected $strategies = [];
    
    /**
     * @var ModularSchemaInterface[]
     */
    protected $schemas = [];
    
    /**
     * @var ModularProcessEnvironment
     */
    protected $env;
    
    
    final public function __construct () 
    {
        $this->env = new ModularProcessEnvironment();
        $this->init();
    }
    
    public function init () 
    {
        
    }
    
    public function run () 
    {
        $this->processSchemas();
        $this->processStrategies();
    }
    
    /**
     * @param ModularStrategyInterface $strategy
     * @param int $order
     * @return $this
     */
    public function addStrategy(ModularStrategyInterface $strategy, $order = self::SECTION_RUN)
    {
        $this->strategies[$order][] = $strategy;
        
        return $this;
    }
    
    public function processSchemas ()
    {
        foreach ($this->schemas as $schema) {
            $this->env->makeFollower($schema);
            $schema->configure($this);
        }
    }
    
    public function processStrategies ()
    {
        foreach ($this->sectionsRun as $section => $runStyle) {
            if (!isset($this->strategies[$section])) {
                continue;
            }
            
            if ($runStyle === self::RUN_PARALLEL) {
                $this->runSectionParallel($section);
            } else {
                $this->runSectionSerial($section);
            }
        }
    }
    
    /**
     * @param $section
     *
     */
    public function runSectionSerial ($section)
    {
        foreach ($this->strategies[$section] as $order => $strategy) {
            $this->_prepareStrategy($strategy);
            $this->_runStrategy($strategy);
        }
    }
    
    public function runSectionParallel ($section)
    {
        $this->prepareStrategiesParallel($section);
        $this->processStrategiesParallel($section);
    }
    
    public function prepareStrategiesParallel ($section)
    {
        foreach ($this->strategies[$section] as $strategy) {
            $this->_prepareStrategy($strategy);
        }
    }
    
    public function processStrategiesParallel ($section)
    {
        foreach ($this->strategies[$section] as $strategy) {
            $this->_runStrategy($strategy);
        }
    }
    
    /**
     * @param ModularStrategyInterface $strategy StrategyProto
     *
     * @return bool
     */
    protected function _prepareStrategy (ModularStrategyInterface $strategy)
    {
        $this->env->makeFollower($strategy);
        
        if (!$strategy->shouldProcess()) {
            return false;
        }
        
        $strategy->prepare();
        
        return true;
    }
    
    
    protected function _runStrategy (ModularStrategyInterface $strategy)
    {
        if (!$strategy->shouldProcess()) {
            return false;
        }
        
        $strategy->run();
        
        $this->afterStrategyRun($strategy);
        
        return true;
    }
    
    /**
     * @param ModularStrategyInterface $strategy
     */
    protected function afterStrategyRun($strategy) {
        
    }
    
    /**
     * @param ModularContainerProto $container
     *
     * @return $this
     */
    public function setContainer($container)
    {
        $this->env->setContainer($container);
        return $this;
    }
    
    /**
     * @return ModularContainerProto
     */
    public function getContainer()
    {
        return $this->env->getContainer();
    }
    
    /**
     * @param ModularContextProto $context
     *
     * @return $this
     */
    public function setContext($context)
    {
        $this->env->setContext($context);
        
        return $this;
    }
    
    /**
     * @return ModularContextProto
     */
    public function getContext()
    {
        return $this->env->getContext();
    }
    
    /**
     * Установить способ исполнения секции
     *
     * @param $section
     * @param $runStyle
     *
     */
    public function setSectionsRun($section, $runStyle)
    {
        $this->sectionsRun[$section] = $runStyle;
    }
    
    /**
     * @param ModularSchemaInterface $schema
     */
    public function addSchema($schema)
    {
        $this->schemas[] = $schema;
    }
    
    /**
     * @return ModularSchemaInterface[]
     */
    public function getSchemas()
    {
        return $this->schemas;
    }
}