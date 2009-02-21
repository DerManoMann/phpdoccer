<?php

class ParameterContainer extends PDContainer {

    /** 
     * Create parameter container.
     *
     * @param array docData The doc data; default is <code>array()</code>.
     * @param array codeInfo Details extracted from code rather docs; default is <code>array()</code>.
     */
    public function __construct($docData=array(), $codeInfo=array()) {
        parent::__construct('parameter', $docData, $codeInfo);
    }


    /**
     * Get the field value.
     *
     * @return string Always <code>null</code>.
     */
    public function getValue() {
        return null;
    }

    /**
     * Get the default value.
     *
     * <p>This is available on function and method parameters that have a default value.</p>
     *
     * @return string The value or <code>null</code>.
     */
    public function getDefaultValue() {
        return $this->get('defaultValue');
    }

}

?>
