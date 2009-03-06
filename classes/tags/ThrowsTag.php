<?php

require_once('seeTag.php');

/** 
 * Throws tag.
 */
class ThrowsTag extends SeeTag {

    /**
      * {@inheritDoc}
     */
    public function __construct($name, $text, $docData, $tagFactory) {
        parent::__construct($name, $text, $docData, $tagFactory);
        $explode = preg_split('/[ \t]+/', $text);
        $link = array_shift($explode);
        $this->link_ = $link;
    }

}

?>
