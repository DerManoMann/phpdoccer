<?php

  
/**
 * A parent interface.
 *
 * Some dummy interface.
 *
 * @package test.main
 */
interface ParentInterface {
    const _SHOULD_BE_HIDDEN = 'xx';
    const CHOULD_BE_HIDDEN_ = 'xx';

    /**
     * Some interface foo.
     */
    public function foo();

    /**
     * Some interface deng.
     *
     * @return boolean Some child deng.
     */
    public function deng();

}

?>
