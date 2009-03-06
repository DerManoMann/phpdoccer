<?php

/** 
 * General container for useful stuff and general entry point to access parsed <code>PDContainer</code>.
 */
class PDMediator {
    const DEFAULT_PACKAGE = "@default@";

    private $docContainer_;
    private $logger_;
    private $tagFactory_;
    private $contentHandler_;
    private $config_;
    private $packageMap_;
    private $types_;

	
    /** 
     * Create new parser.
     * 
     * @param PDLogger logger A logger.
     * @param PDTagFactory tagFactory A tag factory.
     * @param PDContentHandler contentHandler A content handler.
     * @param array config The config.
     */
    public function __construct(PDLogger $logger, PDTagFactory $tagFactory, PDContentHandler $contentHandler, $config) {
        $this->logger_ = $logger;
        $this->tagFactory_ = $tagFactory;
        $this->contentHandler_ = $contentHandler;
        if (null != $this->contentHandler_) {
            $this->contentHandler_->setMediator($this);
        }
        $this->config_ = $config;
        $this->docContainer_ = array();
        $this->packageMap_ = array();
        if (array_key_exists('var_types', $config)) {
            $this->types_ = array_map(create_function('$value', 'return trim($value);'), explode(',', $config['var_types']));
        } else {
            $this->types_ = null;
        }
    }


    /**
     * Get the logger.
     *
     * @return PDLogger The logger.
     */
    public function getLogger() {
        return $this->logger_;
    }

    /**
     * Get the tag factory.
     *
     * @return PDTagFactory The tag factory.
     */
    public function getTagFactory() {
        return $this->tagFactory_;
    }

    /**
     * Get the content handler.
     *
     * @return PDContentHandler The content handler.
     */
    public function getContentHandler() {
        return $this->contentHandler_;
    }

    /**
     * Get the configuration.
     *
     * @return array The configuration.
     */
    public function getConfig() {
        return $this->config_;
    }

    /**
     * Get a config value.
     *
     * @param string name The name.
     * @return string The value or <code>null</code>.
     */
    public function getConfigValue($name) {
        if (array_key_exists($name, $this->config_)) {
            return $this->config_[$name];
        }
        return null;
    }

    /**
     * Get all processed doc container.
     *
     * @return array List of <code>PDContainer</code> instances.
     */
    public function getDocContainer($type) {
        return $this->docContainer_;
    }

    /**
     * Get all processed doc container ordered by package.
     *
     * @param string type The container type [eg 'class', 'interface', etc].
     * @return array List of <code>PDContainer</code> instances.
     */
    public function getDocContainerByPackage($type) {
        $list = array();
        foreach ($this->packageMap_ as $package => $typeList) {
            if (array_key_exists($type, $typeList)) {
                $list[$package] = $typeList[$type];
            }
        }

        return $list;
    }

    /**
     * Get the package map.
     *
     * @return array The complete package map.
     */
    public function getPackageMap() {
        return $this->packageMap_;
    }

    /**
     * Add a doc container.
     *
     * @param PDContainer container The container to add.
     */
    public function addDocContainer(PDContainer $container) {
        $this->docContainer_[] = $container;
        $this->addToPackageMap($container);
    }

    /**
     * Sort all data.
     */
    public function sort() {
        ksort($this->packageMap_);
    }

    /**
     * Find a container for the given name and type.
     *
     * @param string name The name.
     * @param string type The container type.
     * @return PDContainer A <code>PDContainer</code> or <code>null</code>.
     * @todo native packages
     */
    public function getContainerForName($name, $type) {
        foreach ($this->packageMap_ as $package => $typeList) {
            if (array_key_exists($type, $typeList)) {
                foreach ($typeList[$type] as $container) {
                    if ($container->getName() == $name) {
                        return $container;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Add a container to the internal package map.
     *
     * @param PDContainer container The container to add.
     */
    protected function addToPackageMap($container) {
        $packageTypes = array('class', 'interface', 'function', 'const', 'field');
        foreach ($packageTypes as $type) {
            if (null !== $container->get($type)) {
                foreach ($container->get($type) as $tc) {
                    if (null === ($package = $tc->getPackage())) {
                        $package = PDMediator::DEFAULT_PACKAGE;
                    }
                    if (!array_key_exists($package, $this->packageMap_)) {
                        $this->packageMap_[$package] = array();
                    }
                    if (!array_key_exists($type, $this->packageMap_[$package])) {
                        $this->packageMap_[$package][$type] = array();
                    }
                    $this->packageMap_[$package][$type][] = $tc;
                }
            }
        }
    }

    /**
     * Validate a given data type.
     *
     * @param string type The type string.
     * @return boolean <code>true</code> if valid.
     */
    public function validateType($type) {
        if (null === $this->types_) {
            return true;
        }
        if (in_array($type, $this->types_)) {
            return true;
        }
        // expect uppercase for class name
        if (ucwords($type) == $type) {
            return true;
        }
        
        $this->logger_->warning('invalid data type: ' . $type);
        return false;
    }

    /**
     * Try to find a matching container for the given link.
     *
     * @param string link The link/url.
     * @return PDContainer A container or <code>null</code>.
     */
    public function findContainerForLink($link) {
		    $pearCompat = false; //TODO
        $packageRegex = '[a-zA-Z0-9_\x7f-\xff .-]+';
        $labelRegex = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
        if ($pearCompat) {
            $regex = '/^(?:('.$packageRegex.')::('.$labelRegex.')::|('.$labelRegex.')::)?(?:('.$labelRegex.')\(\)|\$('.$labelRegex.'))$/';
        } else {
            $regex = '/^(?:('.$packageRegex.')\.)?(?:('.$labelRegex.')#)?('.$labelRegex.')$/';
        }

		    $matches = array();
		    if (!preg_match($regex, $link, $matches)) {
            return null;
        }

        if ($pearCompat) {
            $packageName = $matches[1];
            $className = ($matches[2]) ? $matches[2] : $matches[3];
            $elementName = ($matches[4]) ? $matches[4] : $matches[5];
        } else {
            $packageName = $matches[1];
            $className = $matches[2];
            $elementName = $matches[3];
        }

//echo 'p:'.$packageName.'@c:'.$className.'@e:'.$elementName;

        $container = null;

        if ($packageName) {
            $package = null; //TODO
        }
        if ($className) {
            $class = null; //TODO
        }
        if ($elementName) {
            $element = null; //TODO
        }

        return $container;
    }

    /**
     * Parse out inline tags from within a text string.
     *
     * @param string text The text to parse.
     * @param array docData Containing doc data.
     * @return array List of tags.
     */
    public function parseInlineTags($text, $docData) {
        $tagStrings = preg_split('/{(@.+)}/sU', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($tagStrings) {
            $inlineTags = null;
            foreach ($tagStrings as $tag) {
                if (substr($tag, 0, 1) == '@') {
                    $pos = strpos($tag, ' ');
                    if ($pos !== false) {
                        $name = trim(substr($tag, 0, $pos));
                        $text = trim(substr($tag, $pos + 1));
                    } else {
                        $name = $tag;
                        $text = null;
                    }
                } else {
                    $name = '@text';
                    $text = $tag;
                }
                if ('@text' != $name || !empty($text)) {
                    // ignore empty text blocks
                    $inlineTag = $this->tagFactory_->createTag($name, $text, $docData, $this);
                    $inlineTags[] = $inlineTag;
                }
            }
            $return = $inlineTags;
        }
        return $return;
    }

}

?>
