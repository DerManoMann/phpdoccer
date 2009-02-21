<?php

/**
 * Simple tokenizer.
 *
 * <p>Wrapper around PHP's <code>token_get_all()</code> function.</p>
 */
class PDTokenizer {
    private $tokens_;
    private $index_;
    private $filename_;


    /** 
     * Create new tokenzer.
     *
     * @param string filename The filename.
     */
    public function __construct($filename) {
        $this->filename_ = $filename;
        $this->tokens_ = array();
        $this->index_ = 0;
        $contents = @file_get_contents($filename);
        if ($contents) {
            $this->tokens_ = token_get_all($contents);
        }
    }

    /**
     * Get the filename.
     *
     * @return string The filename.
     */
    public function getFilename() {
        return $this->filename_;
    }

    /**
     * Skip.
     *
     * @param int offset The number of token to skip; default is <code>1</code>.
     */
    public function skip($offset=1) {
        $this->index_ += $offset;
    }

    /**
     * Check if more token available.
     *
     * @return boolean <code>true</code> if more token available.
     */
    public function hasNext() {
        return $this->index_ < count($this->tokens_);
    }

    /**
     * Get the next key, token pair.
     *
     * @return mixed A token or <code>null</code>.
     */
    public function next() {
        if ($this->index_ < count($this->tokens_))  {
            return $this->tokens_[$this->index_++];
        }

        return null;
    }

    /**
     * Peek at token using offset.
     *
     * @param int offset An offset relative to the current position.
     * @return mixed Token or <code>null</code>.
     */
    public function peekOffset($offset) {
        if (($this->index_ + $offset - 1) < count($this->tokens_)) {
            return $this->tokens_[$this->index_ + $offset - 1];
        }

        return null;
    }

    /**
     * Peek at next token.
     *
     * @param mixed type Either a single (int) type or a list of types to peek.
     * @param int offset An optional offset relative to the current position; default is <em>0</em>.
     * @param int limit Optional limit to reduce the peek to a certain number of tokens; default is 
     *  <em>0</em> for no limit.
     * @return string Token value or <code>null</code>.
     */
    public function peekNext($type, $offset=0, $limit=0) {
        $index = $this->index_ + $offset;
        if (!is_array($type)) {
            $type = array($type);
        }
        while (!is_array($this->tokens_[$index]) || !in_array($this->tokens_[$index][0], $type)) {
            ++$index;
            if ($index >= count($this->tokens_) || (0 != $limit && ($this->index_ + $limit) <= $index))  {
                return null;
            }
        }

        return $this->tokens_[$index][1];
    }

    /**
     * Peek at previous token.
     *
     * @param mixed type Either a single (int) type or a list of types to peek.
     * @param int offset An optional offset relative to the current position; default is <em>0</em>.
     * @param int limit Optional limit to reduce the peek to a certain number of tokens; default is 
     *  <em>0</em> for no limit.
     * @return string Token value or <code>null</code>.
     */
    public function peekPrev($type, $offset=0, $limit=0) {
        $index = $this->index_ + $offset;
        if (!is_array($type)) {
            $type = array($type);
        }
        while (!is_array($this->tokens_[$index]) || !in_array($this->tokens_[$index][0], $type)) {
            --$index;
            if (0 > $index || (0 != $limit && ($this->index_ - $limit) >= $index))  {
                return null;
            }
        }

        return $this->tokens_[$index][1];
    }

}

?>
