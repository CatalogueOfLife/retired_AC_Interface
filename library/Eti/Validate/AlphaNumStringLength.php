<?php
class Eti_Validate_AlphaNumStringLength extends Zend_Validate_StringLength
{
    public function isValid ($value)
    {
        if(!is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }
        
        $this->_setValue($value);
        
        // remove non-alphanumeric characters
        $value = preg_replace('#\W#', '', $value);
        
        if ($this->_encoding !== null) {
            $length = iconv_strlen($value, $this->_encoding);
        } else {
            $length = iconv_strlen($value);
        }
        
        if ($length < $this->_min) {
            $this->_error(self::TOO_SHORT);
        }
        
        if (null !== $this->_max && $this->_max < $length) {
            $this->_error(self::TOO_LONG);
        }
        
        if (count($this->_messages)) {
            return false;
        } else {
            return true;
        }
    }
}