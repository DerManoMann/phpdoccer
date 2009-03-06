<?php

/** 
 * See tag.
 */
class SeeTag extends PDTag {
    protected $link_;
    protected $linkText_;


    /**
      * {@inheritDoc}
     */
    public function __construct($name, $text, $docData, $tagFactory) {
        parent::__construct($name, $text, $docData, $tagFactory);
        $this->link_ = null;
        $this->linkText_ = null;
        if (preg_match('/^<a href="(.+)">(.+)<\/a>$/', $text, $matches)) {
            $this->link_ = $matches[1];
            $this->linkText_ = trim($matches[2]);
        } else if (preg_match('/^([^ ]+)([ \t](.*))?$/', $text, $matches)) {
            $this->link_ = $matches[1];
            if (isset($matches[3])) {
                $this->linkText_ = trim($matches[3]);
            }
        }
    }


    /**
      * {@inheritDoc}
     */
    public function toString(PDDoclet $doclet) {
        return $doclet->buildLink($this, $this->link_, $this->linkText_);
    }
	
}

?>
