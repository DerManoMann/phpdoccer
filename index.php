<?php

@ini_set("display_errors", true);
error_reporting(E_ALL^E_STRICT);
include 'main.php';

// make sure all test classes are valid
if (false) {
    include 'test/empty.php';
    include 'test/static.php';
    include 'test/TheInterface.php';
    include 'test/ParentClass.php';
    include 'test/ChildClasss.php';
    include 'test/GrandChildClass.php';
    include 'test/Multi.php';
}


/**
 * HTML logger.
 */
class HTMLLogger implements PDLogger {
    private $quiet_ = false;
    private $verbose_ = true;

    /**
     * Create new logger.
     *
     * @param boolean quiet Is the logger quiet?
     * @param boolean verbose Is the logger verbose?
     */
    public function __construct($quiet=false, $verbose=true) {
        $this->quiet_ = $quiet;
        $this->verbose_ = $verbose;
    }


    /**
     *
     * {@inheritDoc}
     */
    public function isVerbose() {
        return $this->verbose_;
    }

    /**
     * {@inheritDoc}
     */
    public function message($msg) {
        if (!$this->quiet_) {
            echo '<span style="color:green;">', $msg, "</span><br>";
        }
    }

    /**
     * {@inheritDoc}
     */
    public function verbose($msg) {
        if ($this->verbose_) {
            echo '<span style="color:green;">', $msg, "</span><br>";
        }
    }

    /**
     * {@inheritDoc}
     */
    public function warning($msg) {
        echo '<span style="color:yellow;background:black;">', $msg, "</span><br>";
    }

    /**
     * {@inheritDoc}
     */
    public function error($msg) {
        echo '<span style="color:red;">', $msg, "</span><br>";
    }

}

$main = new main();
$main->setLogger(new HTMLLogger());
$main->run(array(dirname(__FILE__).'/phpdoccer.ini', dirname(__FILE__).'/debug.ini'));

?>
