<?php

/**
 * Default content handler that does nothing.
 */
class PDContentHandler {
    protected $mediator_;


    /**
     * Set the mediator.
     *
     * @param PDMediator mediator The mediator.
     */
    public function setMediator(PDMediator $mediator) {
        $this->mediator_ = $mediator;
    }


    /**
     * Accept a container.
     *
     * <p>A callback to allow to reject/ignore certain elements during parsing.</p>
     *
     * <p>The default implementation supports the config options:</p>
     * <ul>
     *  <li><strong>public</strong> <em>[on|off]</em></li>
     *  <li><strong>protected</strong> <em>[on|off]</em></li>
     *  <li><strong>private</strong> <em>[on|off]</em></li>
     *  <li>Container names with trailing '_'</li>
     * </ul>
     *
     * @param PDContainer container The container in question.
     * @return boolean <code>true</code> to accept.
     */
    public function acceptContainer(PDContainer $container) {
        /*
		if ($element->isGlobal() && !$element->isFinal() && !$this->_globals) {
			return true;
		} elseif ($element->isGlobal() && $element->isFinal() && !$this->_constants) {
			return true;
		} elseif ($this->_private) {
			return false;
		} elseif ($this->_protected && ($element->isPublic() || $element->isProtected())) {
			return false;
		} elseif ($this->_public && $element->isPublic()) {
			return false;
		}
         */

		return substr($container->getName(), 0, 1) != '_';
    }

}

?>
