<?php

class FileContainer extends PDContainer {

    /** 
     * Create file container.
     *
     * @param array docData The doc data; default is <code>array()</code>.
     * @param array codeInfo Details extracted from code rather docs; default is <code>array()</code>.
     */
    public function __construct($docData=array(), $codeInfo=array()) {
        parent::__construct('file', $docData, $codeInfo);
    }

}

?>
