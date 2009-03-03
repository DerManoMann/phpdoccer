<?php

class FunctionContainer extends PDContainer {

    /** 
     * Create function container.
     *
     * @param array docData The doc data; default is <code>array()</code>.
     * @param array codeInfo Details extracted from code rather docs; default is <code>array()</code>.
     */
    public function __construct($docData=array(), $codeInfo=array()) {
        parent::__construct('function', $docData, $codeInfo);
    }


    /**
     * Get the return type.
     *
     * @return string The return type.
     */
    public function getReturnType() {
        $type = $this->get('fieldType');
        return null != $type ? $type : 'void';
    }

    /**
     * Get the parameters.
     *
     * @return array List of <code>ParameterContainer</code> instances.
     */
    public function getParameters() {
        $list = array();
        $parameters = $this->get('parameter');
        if (null != $parameters) {
            foreach ($parameters as $parameter) {
                if ($parameter->accept()) {
                    $list[] = $parameter;
                }
            }
        }
        return $list;
    }

}

?>
