<?php

require_once('linkPlainTag.php');

/** 
 * Inline link tag.
 */
class LinkTag extends SeeTag {

    /**
      * {@inheritDoc}
     */
    public function __construct($name, $text, $docData, $mediator) {
		    parent::__construct($name, $text, $docData, $mediator);
    }
	
}

?>
