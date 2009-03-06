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
     * Get the mediator.
     *
     * @return PDMediator The mediator.
     */
    public function getMediator() {
        return $this->mediator_;
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

    /**
     * Handle link generation.
     *
     * @param PDTag tag The tag containing the link.
     * @param string link The link/url.
     * @param string text Optional link text; default is <code>null</code>.
     * @return string The link text.
     */
    public abstract function buildLink(PDTag $tag, $link, $text=null);

    /**
     * Generate the documentation.
     */
    public abstract function generate();

}

?>
