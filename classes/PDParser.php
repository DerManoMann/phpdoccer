<?php

/** 
 * A PHP doc parser based on PhpDoctor.
 *
 * <p>Needs PHP5 to run.</p>
 *
 * @todo Support for PHP native packages
 */
class PDParser {
    private $mediator_;
    private $logger_;
    private $tagFactory_;

    private $tokenizer_;

    /*
     * Parse flags.
     */
    private $open_curly_braces_;
    private $in_parsed_string_;
    private $curly_level_;

    // current container
    private $currentContainer_;

    // current doc comment
    private $currentDocComment_;
    // details extracted from code rather than doc
    private $currentCodeInfo_;

    // container stack
    private $containerStack_;

    private $lineNumber_;

	
    /** 
     * Create new parser.
     * 
     * @param PDmediator mediator A mediator.
     */
    public function __construct(PDMediator $mediator) {
        $this->mediator_ = $mediator;
        $this->logger_ = $mediator->getLogger();
        $this->tagFactory_ = $mediator->getTagFactory();
    }


    /**
     * Parse a doc comment into a doc comment array.
     *
     * @param string comment The comment.
     * @return array Doc and tag data.
     */
    protected function parseDocComment($comment) {
        if ('/**' != substr(trim($comment), 0, 3)) {
            return array();
        }

        $data = array(
            'docComment' => $comment,
            'tags' => array(),
            'visibility' => 'public',
            'abstract' => false,
            'final' => false,
            'static' => false
        );
        
        // split into token
        $commentToken = preg_split('/[\n|\r][ \r\n\t\/]*\*[ \t]*@/', "\n".$comment);
        // match text
        preg_match_all('/^[ \t\/*]*\*\/? ?(.*)[ \t\/*]*$/m', array_shift($commentToken), $matches);

        if (isset($matches[1])) {
            // plain text
            $data['tags']['@text'] = array($this->tagFactory_->createTag('@text', trim(join("\n", $matches[1])), $data, $this->tagFactory_));
        }
        
        // process tags
        foreach ($commentToken as $tag) {
            // strip whitespace and asterix's from beginning
            $tag = preg_replace('/(^[\s\n\r\*]+|\s*\*\/$)/m', ' ', $tag);
            $tag = preg_replace('/[\r\n]+/', '', $tag);
            
            $pos = strpos($tag, ' ');
            if (false !== $pos) {
                $name = trim(substr($tag, 0, $pos));
                $text = trim(substr($tag, $pos + 1), "\n\r \t");
            } else {
                $name = $tag;
                $text = null;
            }
            switch ($name) {
            case 'package':
                // place current element in package
                $data['package'] = $text;
                break;
            case 'var':
                // set variable type
                $data['fieldType'] = $text;
                break;
            case 'access':
                // set access permission
                $data['visibility'] = $text;
                break;
            case 'final':
                // element is final
                $data['final'] = true;
                break;
            case 'abstract':
                // element is abstract
                $data['abstract'] = true;
                break;
            case 'static': 
                // element is static
                $data['static'] = true;
                break;
            default: 
                // other tag
                $tagName = '@'.$name;
                if (!array_key_exists($tagName, $data['tags'])) {
                    $data['tags'][$tagName] = array();
                }
                $data['tags'][$tagName][] = $this->tagFactory_->createTag($tagName, $text, $data, $this->tagFactory_);
            }
        }

        return $data;
    }

    /**
     * Create new container.
     *
     * @param string type The container type.
     * @param string name Optional name.
     */
    protected function createContainer($type, $name=null) {
        $class = ucwords($type).'Container';
        if (!class_exists($class)) {
            require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'container'.DIRECTORY_SEPARATOR . $class . '.php';
        }
        $container = new $class($this->currentDocComment_, $this->currentCodeInfo_);
        $container->set('name', $name);
        $this->currentDocComment_ = array();
        $this->currentCodeInfo_ = array();

        $container->set('filename', $this->tokenizer_->getFilename());
        $container->set('lineNumber', $this->lineNumber_);
        $container->setMediator($this->mediator_);
        return $container;
    }

    /**
     * Reset parser.
     */
    protected function reset() {
        $this->open_curly_braces_ = false;
        $this->in_parsed_string_ = false;
        $this->curly_level_ = 0;

        // current container
        $this->currentContainer_ = $this->createContainer('file');

        // current doc comment
        $this->currentDocComment_ = array();
        // details extracted from code rather than doc
        $this->currentCodeInfo_ = array();

        // container stack
        $this->containerStack_ = array();

        $this->lineNumber_ = 1;
    }

    /**
     * Get the next token and do some other stuff.
     *
     * @return mixed A token.
     */
    protected function nextToken() {
        $token = $this->tokenizer_->next();
        if (is_array($token)) {
            $this->lineNumber_ = $token[2];
        }
        return $token;
    }

    /** 
     * Parse a given unit (file).
     *
     * @param PDTokenizer tokenizer The tokenizer.
     * @return ZMDocContainer The global container for this parser run.
     * @todo set filename on containers
     * @todo line numbers
     * @todo make flags and stacks and stuff class members and have some methods to create container, reset, etc.
	   */
    public function parse(PDTokenizer $tokenizer) {
        $this->tokenizer_ = $tokenizer;
        $this->reset();
        $this->logger_->message('Parsing ...');
        while ($tokenizer->hasNext()) {
            $token = $this->nextToken();
            if (!$this->in_parsed_string_ && is_array($token)) {
                switch ($token[0]) {
                case T_COMMENT:
                    // read comment
                case T_DOC_COMMENT:
                    // catch PHP5 doc comment token too
                    $this->currentDocComment_ = $this->parseDocComment($token[1]);
                    break;
                case T_CLASS:
                    // read class
                    if (null != $this->currentContainer_) {
                        $this->currentContainer_->add($this->currentContainer_);
                        $this->containerStack_[] = $this->currentContainer_;
                    }
                    $this->currentContainer_ = $this->createContainer('class', $tokenizer->peekNext(T_STRING));
                    break;
                case T_INTERFACE:
                    // read interface
                    if (null != $this->currentContainer_) {
                        $this->currentContainer_->add($this->currentContainer_);
                        $this->containerStack_[] = $this->currentContainer_;
                    }

                    $this->currentContainer_ = $this->createContainer('interface', $tokenizer->peekNext(T_STRING));
                    break;
                case T_EXTENDS:
                    // get extends clause
                    $this->currentContainer_->set('parentName', $tokenizer->peekNext(T_STRING));
                    break;
                case T_IMPLEMENTS:
                    // get implements clause
                    $offset = 0;
                    while (null !== ($peekToken = $tokenizer->peekOffset(++$offset))) {
                        if (is_string($peekToken) && '{' == $peekToken) {
                            break;
                        }
                        if ($peekToken[0] == T_STRING) {
                            $interface = $peekToken[1];
                            $this->currentContainer_->add('implements', $peekToken[1]);
                        }
                    }
                    break;
                case T_THROW:
                    // do not just assume that exceptions are created via new
                    if (null !== ($peekToken = $tokenizer->peekNext(array(T_NEW, T_STRING), 0, 2)) && 'new' == $peekToken) {
                        $this->currentContainer_->add('throws', $tokenizer->peekNext(T_STRING));
                    }
                    break;

                // the following six need to be stored somewhere
                case T_PRIVATE:
                    $this->currentCodeInfo_['visibility'] = 'private';
                    break;
                case T_PROTECTED:
                    $this->currentCodeInfo_['visibility'] = 'protected';
                    break;
                case T_PUBLIC:
                    $this->currentCodeInfo_['visibility'] = 'public';
                    break;
                case T_ABSTRACT:
                    $this->currentCodeInfo_['abstract'] = true;
                    break;
                case T_FINAL:
                    $this->currentCodeInfo_['final'] = true;
                    break;
                case T_STATIC:
                    $this->currentCodeInfo_['static'] = true;
                    break;

                case T_FUNCTION:
                    // read function
                    if (null != $this->currentContainer_) {
                        $nextContainer = $this->createContainer('function', $tokenizer->peekNext(T_STRING));
                        $this->currentContainer_->add($nextContainer);
                        $this->containerStack_[] = $this->currentContainer_;
                        $this->currentContainer_ = $nextContainer;
                    }
                    break;
                case T_CURLY_OPEN:
                case T_DOLLAR_OPEN_CURLY_BRACES: 
                    // we must catch this so we don't accidently step out of the current block
                    $this->open_curly_braces_ = true;
                    break;
                case T_STRING:
                    $peekTokenSub1 = $tokenizer->peekOffset(-1);
                    $peekTokenSub2 = $tokenizer->peekOffset(-2);
                    $peekTokenAdd2 = $tokenizer->peekOffset(2);
                    if ('define' == $token[1] && T_CONSTANT_ENCAPSED_STRING == $peekTokenAdd2[0]) {
                        // read global constant
                        $newContainer = $this->createContainer('const', trim($peekTokenAdd2[1], '\''));
                        // skip the token we peeked adhead earlier
                        $tokenizer->skip(3);
                        $value = '';
                        while (';' != ($token = $this->nextToken())) {
                            $value .= (is_array($token) ? $token[1] : $token);
                        }
                        $newContainer->set('value', trim($value, ' ()'));
                        if (0 < count($this->containerStack_)) {
                            $this->containerStack_[0]->add($newContainer);
                        } else {
                            $this->currentContainer_->add($newContainer);
                        }
                    } else if (T_WHITESPACE == $peekTokenSub1[0] && T_CONST == $peekTokenSub2[0]) {
                        // member constant
                        unset($value);
                        do {
                            $token = $this->nextToken();
                            if ('=' == $token) {
                                $value = '';
                            } else if (',' == $token || ';' == $token) {
                                $newContainer = $this->createContainer('const', $tokenizer->peekPrev(array(T_VARIABLE, T_STRING)));
                                $newContainer->set('value', $value);
                                $newContainer->set('fieldType', 'const');
                                $this->currentContainer_->add($newContainer);
                                unset ($value);
                            } else if (isset($value)) {
                                // we've hit a '=' before
                                if (is_array($token)) {
                                    $value .= $token[1];
                                } else {
                                    $value .= $token;
                                }
                            }
                        } while (';' != $token);
                    } else if ('function' == $this->currentContainer_->getType() && 1 == $this->curly_level_) {
                        // function parameter
                        unset($newContainer);
                        do {
                            $token = $this->nextToken();
                            if (',' == $token || '}' == $token) {
                                unset($newContainer);
                            } else if (is_array($token)) {
                                if (T_VARIABLE == $token[0] && !isset($newContainer)) {
                                    $newContainer = $this->createContainer('parameter', $token[1]);
                                    $this->currentContainer_->add($newContainer);
                                    // is there a type hint?
                                    $offset = 0;
                                    do {
                                        $peekToken = $tokenizer->peekOffset(--$offset);
                                        if (is_array($peekToken) && T_STRING == $peekToken[0]) {
                                            $newContainer->set('typeHint', $peekToken[1]);
                                        }
                                    } while ('(' != $peekToken && ',' != $peekToken);
                                } else if (isset($newContainer) && (T_STRING == $token[0] || T_CONSTANT_ENCAPSED_STRING == $token[0])) {
                                    // set value
                                    $newContainer->set('defaultValue', $token[1]);
                                }
                            }
                        } while (')' != $token);
                        // get parent back
                        $this->currentContainer_ = array_pop($this->containerStack_);
                    }
                    break;
                case T_VARIABLE:
                    if ('global' == $this->currentContainer_->getType()) {
                        // global var
                        $newContainer = $this->createContainer('field', $token[1]);

                        // try for var type
                        $lastToken = $tokenizer->peekOffset(-1);
                        $secondLastToken = $tokenizer->peekOffset(-1);
                        if (isset($lastToken[0]) && isset($secondLastToken[0]) && T_STRING == $secondLastToken[0] &&  T_WHITESPACE == $lastToken[0]) {
                            $newContainer->set('fieldType', $secondLastToken[1]);
                        }

                        // fish for default value
                        while ($tokenizer->hasNext()) {
                            $token = $this->nextToken();
                            if ('=' == $token || ';' == $token) {
                                break;
                            }
                        }
                        if ('=' == $token) {
                            $default = '';
                            $offset = 1;
                            do {
                                $peekToken = $tokenizer->peekOffset($offset);
                                if (is_array($peekToken)) {
                                    if ('=' != $peekToken[1]) {
                                        $default .=  $peekToken[1];
                                    }
                                } else {
                                    if ('=' != $peekToken) {
                                        $default .= $peekToken;
                                    }
                                }
                                ++$offset;
                            } while (isset($peekToken) && ';' != $peekToken && ',' != $peekToken && ')' != $peekToken);
                            $newContainer->set('fieldDefault', trim($default, ' ()'));
                        }
                        $this->currentContainer_->add($newContainer);
                    } else if (2 > $this->curly_level_) {
                        // member var
                        unset($value);
                        $peekTokenSub1 = $tokenizer->peekOffset(-1);
                        $peekTokenSub2 = $tokenizer->peekOffset(-2);
                        do {
                            $token = $this->nextToken();
                            if ('=' == $token) {
                                $value = '';
                            } else if (',' == $token || ';' == $token) {
                                $newContainer = $this->createContainer('field', $tokenizer->peekPrev(T_VARIABLE));
                                if (isset($value)) {
                                    $newContainer->set('defaultValue', trim($value));
                                }
                                $this->currentContainer_->add($newContainer);
                                if (T_WHITESPACE == $peekTokenSub1[0] && T_VAR == $peekTokenSub2[0]) {
                                    $newContainer->set('fieldType', 'var');
                                }
                                unset($value);
                            } else if (isset($value)) {
                                if (is_array($token)) {
                                    $value .= $token[1];
                                } else {
                                    $value .= $token;
                                }
                            }
                        } while (';' != $token);
                    }
                    break;
                } // switch
            } else {
                // plain text token
                switch ($token) {
                case '{':
                    // keep track of blocks to ignore body vars
                    if (!$this->in_parsed_string_) {
                        ++$this->curly_level_;
                    }
                    break;
                case '}':
                    // keep track of blocks to ignore body vars
                    if (!$this->in_parsed_string_) {
                        if ($this->open_curly_braces_) {
                            $this->open_curly_braces_ = false;
                        } else {
                            --$this->curly_level_;
                            if (0 == $this->curly_level_ && 0 < count($this->containerStack_)) {
                                $this->logger_->verbose('leaving ' . $this->currentContainer_->get('name'));
                                array_pop($this->containerStack_);
                            }
                        }
                    }
                    break;
                case '"':
                    // catch parsed strings so as to ignore tokens within
                    $this->in_parsed_string_ = !$this->in_parsed_string_;
                    break;
                }
            }
        }

        if (0 < count($this->containerStack_)) {
            $fileContainer = array_pop($this->containerStack_);
        } else {
            $fileContainer = $this->currentContainer_;
        }

        // make sure we do not lose the last active container
        if (null != $this->currentContainer_) {
            $fileContainer->add($this->currentContainer_);
        }

        return $fileContainer;
    }

}

?>
