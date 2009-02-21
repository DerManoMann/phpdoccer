<?php

/** 
 * Parameter tag.
 */
class ParamTag extends PDTag {
    private $fieldName_;
    private $fieldType_;


    /**
      * {@inheritDoc}
     */
    public function __construct($name, $text, $docData, $tagFactory) {
        parent::__construct($name, $text, $docData, $tagFactory);

        $this->fieldName_ = null;
		    $token = preg_split('/[ \t]+/', $text);
		    $this->fieldType_ = array_shift($token);
        if ($this->fieldType_) {
            $this->fieldName_ = trim(array_shift($token), '$');
            $this->text_ = join(' ', $token);
        } else {
            $this->fieldType_ = 'mixed';
        }
    }


    /**
     * Get the field name.
     *
     * @return string The field name
     */
    public function getFieldName() {
        return $this->fieldName_;
    }
	
    /**
     * Get the field type.
     *
     * @return string The field type
     */
    public function getFieldType() {
        return $this->fieldType_;
    }
	
}

?>
