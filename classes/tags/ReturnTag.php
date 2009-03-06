<?php

/** 
 * Return tag.
 */
class ReturnTag extends PDTag {
    private $returnType_;


    /**
     * {@inheritDoc}
     */
    public function __construct($name, $text, $docData, $mediator) {
        parent::__construct($name, $text, $docData, $mediator);
        $explode = preg_split('/[ \t]+/', $text);
        $this->returnType_ = array_shift($explode);
    }


    /**
     * Get the return type.
     *
     * @return string The return type.
     */
    public function getReturnType() {
        return $this->returnType_;
    }
	
}

?>
