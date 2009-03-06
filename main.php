<?php

// mark CLI calls
define('PD_CLI_CALL', defined('STDIN') && isset($argv));

include 'classes/PDDoclet.php';
include 'classes/PDContentHandler.php';
include 'classes/PDMediator.php';
include 'classes/PDLogger.php';
include 'classes/PDTag.php';
include 'classes/PDTagFactory.php';
include 'classes/PDContainer.php';
include 'classes/PDTokenizer.php';
include 'classes/PDParser.php';


/**
 * Main entry point to run PHPDoccer.
 */
class main implements PDLogger, PDTagFactory {
    // default logger
    private $logger_;

    private $config_ = array(
        // phpdoccer config
        'files' => '*.php',
        'ignore' => 'CVS, .svn, .git, _compiled',
        'source_path' => './',
        'doclet_path' => './doclets',
        'subdirs' => 'on',
        'quiet' => 'off',
        'verbose' => 'off',
        // doclet config
        'doclet' => 'debug',
        'taglet_path' => 'classes/tags',
        'format' => 'TEXT'
    );


    /**
     * Default c'tor.
     */
    public function __construct() {
        $this->logger_ = $this;
    }


    /**
     * {@inheritDoc}
     */
    public function isVerbose() {
        return 'on' == $this->config_['verbose'];
    }

    /**
     * {@inheritDoc}
     */
    public function message($msg) {
        if ('on' != $this->config_['quiet']) {
            echo $msg, "\n";
        }
    }

    /**
     * {@inheritDoc}
     */
    public function verbose($msg) {
        if ($this->isVerbose()) {
            echo $msg, "\n";
        }
    }

    /**
     * {@inheritDoc}
     */
    public function warning($msg) {
        if (!defined('STDERR')) { define('STDERR', fopen("php://stderr", "wb")); }
        fwrite(STDERR, 'WARNING: '.$msg."\n");
    }

    /**
     * {@inheritDoc}
     */
    public function error($msg) {
        if (!defined('STDERR')) { define('STDERR', fopen("php://stderr", "wb")); }
        fwrite(STDERR, 'ERROR: '.$msg."\n");
    }

    /**
     * {@inheritDoc}
     */
    public function createTag($name, $text, $docData, PDMediator $mediator) {
        $class = ucwords(substr($name, 1));
        if (!class_exists($class)) {
            $tagletFile = $this->fixPath($this->config_['taglet_path']).substr($name, 1).'.php';
            if (is_file($tagletFile)) {
                // load taglet for this tag
                require_once $tagletFile;
                return new $class($name, $text, $docData, $mediator);
            } else {
                $tagFile = 'classes/tags/'.$class.'Tag.php';
                if (is_file($tagFile)) {
                    // load class for this tag
                    $class .= 'Tag';
                    require_once $tagFile;
                    return new $class($name, $text, $docData, $mediator);
                } else {
                    // create standard tag
                    return new PDTag($name, $text, $docData, $mediator);
                }
            }
        }

        return new $class($name, $text, $docData, $mediator);
    }

    /**
     * Set an alternative logger.
     *
     * @param PDLogger logger A logger.
     */
    public function setLogger(PDLogger $logger) {
        $this->logger_ = $logger;
    }

    /**
     * Load ini file.
     *
     * @param string filename The filename.
     * @return array The options.
     */
    protected function loadConfig($filename) {
        // read config file
        if (is_file($filename)) {
            $options = @parse_ini_file($filename);
            if (0 == count($options)) {
                  $this->logger_->error('Could not parse configuration file or file empty: "'.$filename.'"');
                  exit;
            }
            return $options;
        } else {
            $this->logger_->error('Could not find configuration file: "'.$filename.'"');
            exit;
        }
    }

    /**
     * Turn path into an absolute path using the given prefix.
     *
     * @param string path Path to make absolute.
     * @param string prefix Absolute path to append to relative path.
     * @return string The absolute path.
     */
    protected function makeAbsolutePath($path, $prefix) {
        if (
          substr($path, 0, 1) == '/' || // unix root
          substr($path, 1, 2) == ':\\' || // windows root
          substr($path, 0, 2) == '~/' || // unix home directory
          substr($path, 0, 2) == '\\\\' || // windows network location
          preg_match('|^[a-z]+://|', $path) // url
        ) {
            return $path;
        } else {
            return str_replace('./', '', $this->fixPath($prefix).$path);
        }
    }

    /**
     * Add a trailing slash to a path if it does not have one.
     *
     * @param string path Path to postfix.
     * @return string The fixed path.
     */
    protected function fixPath($path) {
        if (substr($path, -1, 1) != DIRECTORY_SEPARATOR && substr($path, -1, 1) != '\\') {
            return $path . DIRECTORY_SEPARATOR;
        } else {
            return $path;
        }
    }

    /**
     * Build a complete list of file to parse. Expand out wildcards and
     * traverse directories if asked to.
     *
     * @param array files Array of filenames to expand.
     * @param string dir Base dir.
     */
    protected function buildFileList($files, $dir) {
        $list = array();
        $dir = $this->fixPath($dir);

        foreach ($files as $filename) {
            $filename = $this->makeAbsolutePath(trim($filename), $dir);
            $globResults = glob($filename); // switch slashes since old versions of glob need forward slashes
            if ($globResults) {
                foreach ($globResults as $filepath) {
                    $okay = true;
                    foreach ($this->config_['ignore'] as $ignore) {
                        if (strstr($filepath, trim($ignore))) {
                            $okay = false;
                            break;
                        }
                    }
                    if ($okay) {
                          $list[] = realpath($filepath);
                    }
                }
            } else if ('on' != $this->config_['subdirs']) {
                $this->logger_->error('Could not find file "'.$filename.'"');
            }
        }

        if ('on' != $this->config_['subdirs']) {
            // recurse into subdir
            $globResults = glob($dir.'*', GLOB_ONLYDIR); // get subdirs
            if ($globResults) {
                foreach ($globResults as $dirName) {
                    $okay = true;
                    foreach ($this->config_['ignore'] as $ignore) {
                        if (strstr($dirName, trim($ignore))) {
                            $okay = false;
                            break;
                        }
                    }
                    if ($okay && (GLOB_ONLYDIR || is_dir($dirName))) {
                        // handle missing only dir support
                        $list = array_merge($list, $this->buildFileList($files, $this->makeAbsolutePath($dirName, $this->config_['basepath'])));
                    }
                }
            }
        }

		    return $list;
    }
	
    /**
     * Get the configured doclet.
     *
     * @return PDDoclet A doclet or <code>null</code>.
     */
    protected function getDoclet() {
        $docletName = $this->config_['doclet'];
        $docletPath = $this->makeAbsolutePath($this->config_['doclet_path'], $this->config_['basepath']);
        $docletFile = $docletPath.DIRECTORY_SEPARATOR.$docletName.DIRECTORY_SEPARATOR.$docletName.'.php';
        if (is_file($docletFile)) {
            $this->logger_->message('Loading doclet "'.$docletName.'"');
            require_once $docletFile;
            return new $docletName();
        } else {
            $this->logger_->error('Could not find doclet "'.$docletFile.'"');
        }
    }

    /**
     * Create a content handler instance.
     *
     * @return PDContentHandler A content handler.
     */
    protected function createContentHandler() {
        $className = $this->config_['content_handler'];
        if (class_exists($className)) {
            // default
            return new $className();
        }

        // look in ext folder
        $extDir = $this->makeAbsolutePath($this->fixPath('classes/ext'), $this->config_['basepath']);
        $classFile = $extDir.$className.'.php';
        if (is_file($classFile)) {
            $this->logger_->message('Loading content handler "'.$classFile.'"');
            include_once $classFile;
            if (class_exists($className)) {
                return new $className();
            }
        } else {
            // try include path
            @include_once $className.'.php';
            if (class_exists($className)) {
                return new $className();
            }
            $this->logger_->warning('Could not find content handler "'.$className.'"');
            return null;
        }
    }

	/**
	 * Get the current time in microseconds.
	 *
	 * @return int Timestamp.
	 */
	private function getTime() {
		$microtime = explode(' ', microtime());
		return $microtime[0] + $microtime[1];
    }
	

    /**
     * Run.
     *
     * @param array configList Configuration filename list.
     * @return PDDoclet The doclet for this run.
     */
    public function run($configList) {
        if (0 == count($configList)) {
            $main->error('Missing ini filename argument');
            exit;
        }

        $start_time = $this->getTime();

        $this->config_['basepath'] = $this->fixPath(dirname($configList[0]));

        // reverse, so the first has the highest priority
        $configList = array_reverse($configList);

        // load config(s)
        foreach ($configList as $file) {
            $config = $this->loadConfig($file);
            // handle defaults
            $this->config_ = array_merge($this->config_, $config);
        }

        // general post processing
        $this->config_['source_path'] = $this->fixPath($this->makeAbsolutePath($this->config_['source_path'], $this->config_['basepath']));
        $this->config_['ignore'] = explode(',', $this->config_['ignore']);
        $this->config_['files'] = array_unique($this->buildFileList(explode(',', $this->config_['files']), $this->config_['source_path']));
        if (0 == count($this->config_['files'])) {
            $this->logger_->error('Could not find any files to parse');
            exit;
        }

        $mediator = new PDMediator($this->logger_, $this, $this->createContentHandler(), $this->config_);
        $parser = new PDParser($mediator);
        foreach ($this->config_['files'] as $file) {
            $this->logger_->message('Reading file "'.$file.'"');
            $tokenizer = new PDTokenizer($file);
            $container = $parser->parse($tokenizer);
            $mediator->addDocContainer($container);
        }

        $mediator->sort();

        $doclet = $this->getDoclet();
        $doclet->setMediator($mediator);
        $doclet->generate();

		    $this->message('Done ('.round($this->getTime() - $start_time, 2).' seconds)');
        return $doclet;
    }

}

// execute with command line parameters
if (PD_CLI_CALL) {
    array_shift($argv);
    $main = new main();
    $main->run($argv);
}

?>
