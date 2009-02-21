<?php

/**
 * Factory of <code>PDTag<code> instances.
 */
interface PDTagFactory {

	/**
   * Create a tag. 
   *
   * <p>Tries to instantiate the best matching tag implementation for the given tag name.</p>
	 *
	 * @param string name The name of the tag>
	 * @param string text The tag text.
	 * @param array docData The docData referencing this tag.
	 * @param PDTagFactory tagFactory A tag factory to be used by the new tag.
	 * @return PDTag A tag or <code>null</code>.
	 */
	public function createTag($name, $text, $docData, $tagFactory);
	
}

?>
