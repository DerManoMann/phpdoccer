<?php

/** 
 * The debugging doclet.
 */
class Debug extends PDDoclet {
    private $depth_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct(true);
        $this->depth_ = 1;
    }


    /**
     * Generate the documentation.
     */
    public function generate() {
        ob_start();

        // iterate over all container
        //foreach ($this->mediator_->getDocContainerByPackage('class') as $package => $containerList) {
        foreach ($this->mediator_->getPackageMap() as $package => $typeList) {
            echo $this->showDepth(), $package, "\n";
            foreach ($typeList as $type => $containerList) {
                if ('class' == $type) {
                    $this->classDoc($containerList);
                } else  if ('interface' == $type) {
                    $this->classDoc($containerList);
                } else {
                    echo $type."<BR>";
                }
            }
        }

        $output = ob_get_clean();
        if ('HTML' == $this->config_['format']) {
            $output = nl2br($output);
        }
        echo $output;
    }


    /** 
     * Return the depth indicator string
     *
     * @return str
     */
    private function showDepth() {
        $space = ('HTML' == $this->config_['format']) ? '&nbsp;&nbsp;&nbsp;' : ' ';
        return str_repeat('|'.$space, $this->depth_).'- ';
    }

    /** 
     * Dump classes and interfaces.
     *
     * @param array classList List of <code>ClassContainer</code> instances.
     */
    protected function classDoc($classList) {
        $this->depth_++;
        foreach ($classList as $container) {
            echo $this->showDepth(), $container->getVisibility();
            echo ' ', $container->getType(), ' ', $container->getName();
            if (null != ($parent = $container->getParent())) {
                echo ' extends ', $parent->getName();
            }
            if ('class' == $container->getType()) {
                $interfaces = $container->getInterfaces();
                if (0 < count($interfaces)) {
                    echo ' implements ';
                    foreach($interfaces as $interface) {
                        echo $interface->getName(), ' ';
                    }
                }
            }
            echo $this->buildFileInfo($container), "\n";
            $this->fieldDoc($container->getConsts(), false);
            $this->fieldDoc($container->getFields(), true);
            if ('class' == $container->getType()) {
                $this->ctorDoc($container->getCtors());
            }
            $this->methodDoc($container->getMethods());
        }
        $this->depth_--;
    }
	
    /** 
     * Output fieldDoc
     *
     * @param array fieldList List of const/field containers.
     */
    protected function fieldDoc($fieldList, $showAccess=false) {
        $this->depth_++;
        foreach ($fieldList as $container) {
            $type = $container->getType();
            echo $this->showDepth();
            if ($showAccess) {
                echo $container->getVisibility(), ' ';
            }
            if ('const' == $container->getType()) {
                echo 'const';
            }
            if ($container->isFinal()) {
                echo 'final';
            }
            echo ' ', $container->getName();
            if (null != ($value = $container->getValue())) {
                echo ' = ', $value;
            } else if (null != ($value = $container->getDefaultValue())) {
                echo ' = ', $value;
            }
            echo "\n";
        }
        $this->depth_--;
    }

    /** 
     * Output constructor and destructor.
     *
     * @param array ctorList List of constructor (and optionally destructor).
     */
    protected function ctorDoc($ctorList) {
        $this->depth_++;
        foreach ($ctorList as $container) {
            echo $this->showDepth(), $container->getVisibility(), ' ', $container->getName(), ' ';
            echo $this->buildFunctionSignature($container);
            echo $this->buildFileInfo($container);
            echo "\n";
            $this->fieldDoc($container->getParameters());
        }
        $this->depth_--;
    }

    /** Output methodDoc
     *
     * @param array methodList List of methods.
     */
    protected function methodDoc($methodList) {
        $this->depth_++;
        foreach ($methodList as $container) {
            $name = $container->getName();
            echo $this->showDepth(), $container->getVisibility(), ' ', $container->getName(), ' ';
            echo $this->buildFunctionSignature($container);
            echo $this->buildFileInfo($container);
            echo "\n";
            //$this->fieldDoc($container->get('parameter'));

            /*
            $type = $method->returnType();
            echo $this->showDepth(), $method->modifiers();
            if ($type) {
            echo $type->toString(), ' ';
            } else {
            echo 'void ';
            }
            echo $method->name(), $method->flatSignature(), "\n";
            $this->fieldDoc($method->parameters());
            $this->methodDoc($method->functions());
            $exceptions =& $method->thrownExceptions();
            if ($exceptions) {
            foreach ($exceptions as $exception) {
            echo str_repeat('|', $this->depth_ + 1), '- throws ';
            if (is_object($exception)) {
            echo $exception->name(), "\n";
            } else {
            echo $exception, "\n";
            }
            }
            }
             */
        }
        $this->depth_--;
    }
	
}

?>
