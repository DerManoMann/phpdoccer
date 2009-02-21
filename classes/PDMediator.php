<?php

/** 
 * General container for useful stuff and general entry point to access parsed <code>PDContainer</code>.
 */
class PDMediator {
    private $docContainer_;
    private $logger_;
    private $tagFactory_;
    private $contentHandler_;
    private $config_;
    private $packageMap_;

	
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
                        $package = '@default@';
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

}

?>
