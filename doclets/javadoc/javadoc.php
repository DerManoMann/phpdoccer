<?php

/** 
 * The javadoc alike doclet.
 */
class Javadoc extends PDDoclet {

    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct(true);
    }


    /**
     * {@inheritDoc}
     */
    public function generate() {
        echo 'javadoc';
    }
	
    /**
     * {@inheritDoc}
     */
    public function buildLink(PDTag $tag, $link, $text=null) {
        if (empty($text)) {
            $text = $link;
        }

        $container = $this->mediator_->findContainerForLink($link);
        if (null != $container) {
            //TODO:
            //$package =& $this->_parent->containingPackage();
            //$path = str_repeat('../', $package->depth() + 1).$element->asPath();
            return '<a href="'.$path.'">'.$link.'</a>';
        } else if (1 === preg_match('/^(https?|ftp):\/\//', $link)) {
            return '<a href="'.$link.'">'.$text.'</a>';
        }

        if ($tag instanceof PlainLinkTag) {
            $link = '<code>' . $link . '</code>';
        }

        return $link;
    }

}

?>
