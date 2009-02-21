<?php

/**
 * Default content handler that does nothing.
 */
class ZMContentHandler extends PDContentHandler {

    /**
     * {@inheritDoc}
     */
    public function acceptContainer(PDContainer $container) {
        if (substr($container->getName(), -1) == '_') {
            return false;
        }
        return parent::acceptContainer($container);
    }

}

?>
