<?php

namespace Inspector;

use Inspector\Inspection\InspectionInterface;

use ReflectionClass;

class Inspector implements InspectorInterface
{
    private $inspections = array();
    private $container;
    
    public function __construct($container)
    {
        $this->container = $container;
    }
    
    public function addInspection(InspectionInterface $inspection)
    {
        $this->inspections[] = $inspection;
    }
    public function getInspections()
    {
        return $this->inspections;
    }
    
    public function runInspection(InspectionInterface $inspection)
    {
        $className = $inspection->getClassName();
        $methodName = $inspection->getMethodName();
        
        $reflector = new ReflectionClass($className);
        $method = $reflector->getConstructor();
        $arguments = array();
        
        // Inject requested constructor arguments
        if ($method) {
            foreach ($method->getParameters() as $p) {
                if ($p->getName() == 'db') {
                    $arguments[] = $this->container['db'];
                }
            }
        }
        $instance = $reflector->newInstanceArgs($arguments);
        
        foreach ($reflector->getMethods() as $method) {
            if ($method->getName()==$methodName) {
                if ($method->isPublic()) {
                    $instance->$methodName($inspection);
                }
            }
        }
    }
    
    public function run()
    {
        foreach ($this->inspections as $inspection) {
            $this->runInspection($inspection);
        }
    }
}
