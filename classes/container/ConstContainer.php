<?php

class ConstContainer extends PDContainer {

    /** 
     * Create const container.
     *
     * @param array docData The doc data; default is <code>array()</code>.
     * @param array codeInfo Details extracted from code rather docs; default is <code>array()</code>.
     */
    public function __construct($docData=array(), $codeInfo=array()) {
        parent::__construct('const', $docData, $codeInfo);
    }


    /**
     * Get the const value.
     *
     * <p>The initial value of a global or class const.</p>
     *
     * @return string The value or <code>null</code>.
     */
    public function getValue() {
        return $this->get('value');
    }

    /**
     * Get the default value.
     *
     * @return string Always <code>null</code>.
     */
    public function getDefaultValue() {
        return null;
    }

}

?>
