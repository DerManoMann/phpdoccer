<?php

class ClassContainer extends PDContainer {

    /** 
     * Create class container.
     *
     * @param array docData The doc data; default is <code>array()</code>.
     * @param array codeInfo Details extracted from code rather docs; default is <code>array()</code>.
     */
    public function __construct($docData=array(), $codeInfo=array()) {
        parent::__construct('class', $docData, $codeInfo);
    }

    /**
     * Get all class fields.
     *
     * @return array List of <code>FieldContainer</code> instances.
     */
    public function getFields() {
        $field = $this->get('field');
        return null !== $field ? $field : array();
    }

    /**
     * Get all class consts.
     *
     * @return array List of <code>ConstContainer</code> instances.
     */
    public function getConsts() {
        $const = $this->get('const');
        return null !== $const ? $const : array();
    }

    /**
     * Get the parent class (if any).
     *
     * @return ClassContainer A parent or <code>null</code>.
     */
    public function getParent() {
        return $this->mediator_->getContainerForName($this->get('parentName'), $this->getType());
    }

    /**
     * Get the interfaces this class implements.
     *
     * @return array List of interfaces.
     */
    public function getInterfaces() {
        $list = array();
        $interfaces = $this->get('implements');
        if (null != $interfaces) {
            foreach ($interfaces as $interface) {
                $list[] = $this->mediator_->getContainerForName($interface, 'interface');
            }
        }
        return $list;
    }

    /**
     * Get the constructor and descructor methods (if any).
     *
     * @param boolean constructorOnly Optional flag to only lookup the constructor; default is <code>false</code>.
     * @return array List of functions.
     */
    public function getCtors($constructorOnly=true) {
        $list = array();
        $functions = $this->get('function');
        if (null != $functions) {
            foreach ($functions as $function) {
                $name = $function->getName();
                if ('__construct' == $name || $this->getName() == $name) {
                    $list[] = $function;
                }
                if (!$constructorOnly && '__destruct' == $name) {
                    $list[] = $function;
                }
            }
        }
        return $list;
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
                $name = $function->getName();
                if ('__construct' != $name && $this->getName() != $name) {
                    if ($function->accept()) {
                        $list[] = $function;
                    }
                }
            }
        }
        return $list;
    }

}

?>
