<?php

/**
 * Container base class.
 */
class PDContainer {
    protected $type_;
    protected $docData_;
    protected $codeInfo_;
    protected $properties_;
    protected $mediator_;
    protected $contentHandler_;


    /** 
     * Create new container.
     *
     * @param string tpye The container type.
     * @param array docData The doc data; default is <code>array()</code>.
     * @param array codeInfo Details extracted from code rather docs; default is <code>array()</code>.
     */
    public function __construct($type, $docData=array(), $codeInfo=array()) {
        $this->type_ = $type;
        $this->docData_ = $docData;
        $this->codeInfo_ = $codeInfo;
        // some defaults
        $this->properties_ = array();
    }


    /**
     * Get the type.
     *
     * @return string The type.
     */
    public function getType() {
        return $this->type_;
    }

    /**
     * Get the name.
     *
     * @return string The name.
     */
    public function getName() {
        return $this->get('name');
    }

    /**
     * Get the doc data.
     *
     * @return array The doc data.
     */
    public function getDocData() {
        return $this->docData_;
    }

    /**
     * Get the code info.
     *
     * @return array The code info.
     */
    public function getCodeInfo() {
        return $this->codeInfo_;
    }

    /**
     * Get all proeprties.
     *
     * @return array The properties.
     */
    public function getProperties() {
        return $this->properties_;
    }

    /**
     * Get the filename.
     *
     * @return string The filename.
     */
    public function getFilename() {
        return $this->get('filename');
    }

    /**
     * Get the line number.
     *
     * @return int The line number.
     */
    public function getLineNumber() {
        return $this->get('lineNumber');
    }

    /**
     * Build a simple file info string.
     *
     * @return string A formatted string.
     */
    protected function fileInfo() {
        return ' [' . basename($this->getFilename()) . ' at line ' . $this->getLineNumber() . ']';
    }

    /**
     * Check if a container is acceptable.
     *
     * @param PDContainer container The container in question.
     * @return boolean <code>true</code> to accept.
     */
    protected function accept() {
        if (null != $this->contentHandler_) {
            return $this->contentHandler_->acceptContainer($this);
        }
        return true;
    }

    /**
     * Log.
     * 
     * @param string type The log level.
     * @param string msg The message.
     */
    protected function log($type, $msg) {
        $this->mediator_->getLogger()->$type($this->type_ . ' ' . $this->get('name') . ' ' . $this->fileInfo() . ': ' . $msg);
    }

    /**
     * Get the visibility of this container.
     *
     * @return string Either of 'public', 'protected', 'private'.
     */
    public function getVisibility() {
        return $this->getMerged('@visibility');
    }

    /**
     * Check if the container is final.
     *
     * @return boolean <code>true</code> if final.
     */
    public function isFinal() {
        return $this->getMerged('@final');
    }

    /**
     * Get data from merged doc and parse info.
     *
     * @param string name The name.
     * @param boolean preferDocData Optional flag to indicate whether to return docData
     * or parseInfo in case they differ; default is <code>true</code>.
     * @return string The value or <code>null</code>.
     */
    protected function getMerged($name, $preferDocData=true) {
        $value = null;
        if (array_key_exists('tags', $this->docData_) && array_key_exists($name, $this->docData_['tags'])) {
            $value = $this->docData_['tags'][$name];
        }
        if (array_key_exists($name, $this->codeInfo_)) {
            if ($value != $this->codeInfo_[$name]) {
                $this->log('warning', 'doc mismatch on ' . $name);
                if (!$preferDocData) {
                    $value = $this->codeInfo_[$name];
                }
            }
        }
        return $value;
    }

    /**
     * Get the package.
     *
     * <p>Since everything is organized via packages, this deserves a dedicated method.
     *
     * @return string The package name or <code>null</code> for the default package.
     * @todo Add code to handle native packages.
     */
    public function getPackage() {
        if (array_key_exists('package', $this->docData_)) {
            return $this->docData_['package'];
        }

        return null;
    }

    /**
     * Set the mediator.
     *
     * @param PDMediator mediator The mediator data.
     */
    public function setMediator($mediator) {
        $this->mediator_ = $mediator;
        $this->contentHandler_ = $mediator->getContentHandler();
    }

    /**
     * Set a property.
     *
     * @param string name The name.
     * @param mixed value The value.
     */
    public function set($name, $value) {
        $this->properties_[$name] = $value;
    }

    /**
     * Get a property.
     *
     * @param string name The name.
     * @return mixed The value.
     */
    public function get($name) {
        if ($this->has($name)) {
            return $this->properties_[$name];
        }

        return null;
    }

    /**
     * Check for a property.
     *
     * @param string name The name.
     * @return booelan <code>true</code> if the property is set.
     */
    public function has($name) {
        return array_key_exists($name, $this->properties_);
    }

    /**
     * Add a property value.
     *
     * <p>Same as <code>set(string,mixed)</code>, except that this method will not override
     * existing data, but allow to store multiple values.</p>
     *
     * @param mixed name The name [or a <code>PDContainer</code>).
     * @param mixed value The value.
     */
    public function add($name, $value=null) {
        if ($name instanceof PDContainer) {
            $value = $name;
            $name = $value->getType();
        }
        if (!$this->has($name)) {
            // not set
            $this->properties_[$name] = array($value);
        } else if (is_array($this->properties_[$name])) {
            // already a list
            $this->properties_[$name][] = $value;
        } else {
            // single value
            $this->properties_[$name] = array($this->properties_[$name], $value);
        }
    }

    /**
     * Return printable version.
     */
    public function __toString() {
        return '['.get_class($this).' name='.$this->get('name').']';
    }

}

?>
