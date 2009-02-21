<?php

class InterfaceContainer extends PDContainer {

    /** 
     * Create interface container.
     *
     * @param array docData The doc data; default is <code>array()</code>.
     * @param array codeInfo Details extracted from code rather docs; default is <code>array()</code>.
     */
    public function __construct($docData=array(), $codeInfo=array()) {
        parent::__construct('interface', $docData, $codeInfo);
    }


    /**
     * Get the parent interface (if any).
     *
     * @return InterfaceContainer A parent or <code>null</code>.
     */
    public function getParent() {
        return $this->mediator_->getContainerForName($this->get('parentName'), $this->getType());
    }

    /**
     * Get all class fields.
     *
     * @return array List of <code>FieldContainer</code> instances.
     */
    public function getFields() {
        if (null != ($fields = $this->get('field'))) {
            $list = array();
            foreach ($fields as $field) {
                if ($field->accept()) {
                    $list[] = $field;
                }
            }
        }

        return null !== $fields ? $list : array();
    }

    /**
     * Get all class consts.
     *
     * @return array List of <code>ConstContainer</code> instances.
     */
    public function getConsts() {
        if (null != ($consts = $this->get('const'))) {
            $list = array();
            foreach ($consts as $const) {
                if ($const->accept()) {
                    $list[] = $const;
                }
            }
        }

        return null !== $consts ? $list : array();
    }

    /**
     * Get the methods of this class.
     *
     * @return array List of methods.
     */
    public function getMethods() {
        $list = array();
        $functions = $this->get('function');
        if (null != $functions) {
            foreach ($functions as $function) {
                if ($function->accept()) {
                    $list[] = $function;
                }
            }
        }
        return $list;
    }

}

?>
