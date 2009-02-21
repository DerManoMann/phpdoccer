<?php

/**
 * A global const.
 *
 * @var string The x.
 */
$foo = 'x';

  
/**
 * An abstract parent class.
 *
 * Has some more text without any HTML.
 *
 * @package test.main
 */
abstract class ParentClass {
    /** 
     * A parent class field.
     *
     * @var string
     */
    public $doh = 'dohvalue';
    private static $foobar;

    /**
     * Parent method foo.
     *
     * @return boolean Some parent foo.
     */
    public function foo() {
    }

    /**
     * Protected parent method bar.
     *
     * @return boolean Some parent bar.
     */
    protected function bar() {
    }

    /**
     * Parent method deng.
     *
     * @return integer Some deng.
     */
    private function deng() {
    }

    /**
     * Abstract method.
     */
    public abstract function abs();
}

?>
