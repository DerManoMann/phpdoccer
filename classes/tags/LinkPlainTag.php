<?php

require_once('seeTag.php');

/** 
 * Inline link tag.
 */
class LinkPlainTag extends SeeTag {

    /**
      * {@inheritDoc}
     */
    public function __construct($name, $text, $docData, $mediator) {
        parent::__construct($name, $text, $docData, $mediator);
        $this->link_ = null;
        $this->linkText_ = null;
        $explode = preg_split('/[ \t]+/', $text);
        $link = array_shift($explode);
        if ($link) {
            $this->link_ = $link;
            $this->linkText_ = join(' ', $explode);
        }
    }

}

?>
