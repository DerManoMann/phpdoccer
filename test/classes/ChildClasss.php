<?php

define('SOME_GLOBAL_FOO', 'SOME_GLOBAL_FOO_bar');
define('SOME_GLOBAL_FOO2','SOME_GLOBAL_FOO_bar2');
define('SOME_GLOBAL_FOO3',
  SOME_GLOBAL_FOO . 'SOME_GLOBAL_FOO_bar3'
);
 
/**
 * Another global.
 */
$glob = 'kugel';

/**
 * {@inheritDoc}
 *
 * plus some child class.
 *
 * @package test.main
 */
class ChildClass extends ParentClass implements ParentInterface {
    /**
     * A class const.
     */
    const SOME_CLASS_CONST = 'gnar';
    var $some_var = 'foo';

    /**
     * Child c'tor doc.
     */
    public function __construct() {
    }

    /**
     * {@inheritDoc}
     */
    public function foo() {
      define('SOME_METHOD_FOO', 'SOME_METHOD_FOO_bar');
    }

    /**
     * Child method deng.
     *
     * @return boolean Some child deng.
     * @throws Exception
     */
    public function deng() {
      throw new Exception();
    }

    /**
     * Child method with arguments.
     *
     * @param string foo The foo parameter.
     */
    protected final function yoo(ParentClass $foo) {
    }

    /**
     * Some other child method with arguments.
     *
     * @param string fu The fu parameter.
     */
    protected function hoooaa($fu='hihi') {
    }

    /**
    * Implement abstract method.
    */
    public function abs() {
    }

}

?>
