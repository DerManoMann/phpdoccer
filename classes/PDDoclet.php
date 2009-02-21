<?php

/**
 * A doclet.
 */
abstract class PDDoclet {
    protected $mediator_;
    protected $config_;
    protected $showFileInfo_;


    /**
     * Create instance.
     *
     * @param boolean showFileInfo Optional flag; default is <code>true</code>
     */
    public function __construct($showFileInfo=true) {
        $this->showFileInfo_ = $showFileInfo;
    }

    /**
     * Set the mediator.
     *
     * @param PDMediator mediator The mediator.
     */
    public function setMediator(PDMediator $mediator) {
        $this->mediator_ = $mediator;
        $this->config_ = $mediator->getConfig();
    }

    /**
     * Build a simple file info string.
     *
     * @param PDContainer container A container.
     * @return string A formatted string.
     */
    public function buildFileInfo(PDContainer $container) {
        if ($this->showFileInfo_) {
            return ' [' . basename($container->getFilename()) . ' at line ' . $container->getLineNumber() . ']';
        }
        return '';
    }

    /**
     * Create a simple function signature string for the given method/function.
     *
     * @param FunctionContainer function The function container.
     * @return string A formatted signature.
     */
    public function buildFunctionSignature(PDContainer $function) {
        $s = '';
        $parameters = $function->getParameters();
        if (0 < count($parameters)) {
            $token = array();
            foreach ($parameters as $parameter) {
                $token[] = $parameter->getName();
            }
            $s = implode($token, ',');
        }
        return '(' . $s . ')';
    }

/*
		$signature = '';
		$myPackage =& $this->containingPackage();
		foreach($this->_parameters as $param) {
			$type =& $param->type();
			$classDoc =& $type->asClassDoc();
			if ($classDoc) {
				$packageDoc =& $classDoc->containingPackage();
				$signature .= '<a href="'.str_repeat('../', $myPackage->depth() + 1).$classDoc->asPath().'">'.$classDoc->name().'</a> '.$param->name().', ';
			} else {
				$signature .= $type->typeName().' '.$param->name().', ';
			}
		}
		return '('.substr($signature, 0, -2).')';
 */


    /**
     * Generate the documentation.
     */
    public abstract function generate();

}

?>
