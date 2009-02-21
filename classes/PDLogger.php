<?php

/**
 * Simple logger for all to use.
 */
interface PDLogger {

	/**
	 * Is verbose?
	 *
	 * @return boolean <code>true</code> if verbose.
	 */
	public function isVerbose();

	/**
	 * Write a message to standard output.
	 *
	 * @param string msg Message to output.
	 */
	public function message($msg);

	/**
	 * Write a message to standard output.
	 *
	 * @param string msg Message to output.
	 */
	public function verbose($msg);

	/**
	 * Write a warning message to standard error.
	 *
	 * @param string msg Warning message to output.
	 */
	public function warning($msg);

	/**
	 * Write an error message to standard error.
	 *
	 * @param string msg Error message to output.
	 */
	public function error($msg);

}

?>
