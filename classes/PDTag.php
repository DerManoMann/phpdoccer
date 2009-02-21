<?php

/** 
 * Represents a documentation tag, e.g. @since, @author, @version. Given a tag
 * (e.g. "@since 1.2"), holds tag name (e.g. "@since") and tag text (e.g.
 * "1.2").
 */
class PDTag {
    protected $name_;
    protected $text_;
    protected $docData_;
    protected $mediator_;


    /**
     * Create new tag.
     *
     * @param string name The name of the tag (including @).
     * @param string text The contents of the tag.
     * @param array docData The context doc data.
     * @param PDMediator mediator A mediator.
     */
    public function __construct($name, $text, $docData, $mediator) {
        $this->name_ = $name;
        $processedText = '';
        foreach(explode("\n", $text) as $line) {
            $processedText .= $line."\n"; // keep formatting
        }
        $this->text_ = substr($processedText, 0, -1);
        $this->docData_ = $docData;
        $this->mediator_ = $mediator;
    }

    /** 
     * Get name of this tag.
     *
     * @return string The name.
     */
    public function getName() {
        return $this->name_;
    }

    /** 
     * Get the tag text.
     *
     * @return string The text.
     */
    public function getText() {
        return $this->text_;
    }

    /**
     * For documentation comment with embedded @link tags, return the array of
     * tags. 
     *
     * <p>Within a comment string "This is an example of inline tags for a
     * documentaion comment {@link Doc commentlabel}", where inside the inner
     * braces, the first "Doc" carries exactly the same syntax as a SeeTag and
     * the second "commentlabel" is label for the HTML link, will return an array
     * of tags with first element as tag with comment text "This is an example of
     * inline tags for a documentation comment" and second element as SeeTag with
     * referenced class as "Doc" and the label for the HTML link as
     * "commentlabel".</p>
     *
     * @return array List of <code>PDTag</code> instances.
     */
    public function getInlineTags() {
        return $this->parseInlineTags($this->text());
    }

    /**
     * Return the first sentence of the comment as tags. Includes inline tags
     * (i.e. {@link reference} tags) but not regular tags. 
     *
     * <p>Each section of plain text is represented as a Tag of kind "Text". 
     * Inline tags are represented as a SeeTag of kind "link".</p>
     * <p>The sentence ends at the first period that is followed by a space, tab,
     * a line terminator, at the first tagline, or at closing of a HTML block element
     * (&lt;p&gt; &lt;h1&gt; &lt;h2&gt; &lt;h3&gt; &lt;h4&gt; &lt;h5&gt; &lt;h6&gt; &lt;hr&gt; &lt;pre&gt;).</p>
     *
     * @return array List of <code>PDTag</code> instances.
     * @todo This method does not act as described but should be altered to do so
     */
    public function getFirstSentenceTags() {
        if (preg_match('/^(.+)\.( |\t|\r|\n|<\/p>|<\/?h[1-6]>|<hr)/sU', $this->getText(), $matches)) {
            return $this->parseInlineTags($matches[1].'.'.$matches[2]);
        } else {
            return array($this);
        }
    }

    /**
     * Parse out inline tags from within a text string.
     *
     * @param string text The text to parse.
     * @return array List of tags.
     */
    protected function parseInlineTags($text) {
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
                $data = null;
                $inlineTag = $this->mediator_->getTagFactory()->createTag($name, $text, $this->docData_, $this->mediator_);
                $inlineTags[] = $inlineTag;
            }
            $return = $inlineTags;
        }
        return $return;
    }

}

?>
