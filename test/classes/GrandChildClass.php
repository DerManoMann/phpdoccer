<?php

  
/**
 * {@inheritDoc}
 */
class GrandChildClass extends ChildClass {
    /**
     * Some grand child c'tor doc.
     *
     * @param string foo Some foo.
     */
    public function __construct($foo='xx') {
    }

    /**
     * {@inheritDoc}
     */
    public function foo() {
        // from ChildClass
    }

    /**
     * {@inheritDoc}
     *
     * @final
     */
    public function bar() {
        // from ParentClass
    }
}

?>
