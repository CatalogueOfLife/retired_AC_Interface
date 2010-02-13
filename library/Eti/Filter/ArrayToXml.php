<?php
/**
 * @see Zend_Filter_Interface
 */
require_once 'Zend/Filter/Interface.php';
/**
 * Annual Checklist Interface
 *
 * Class Eti_Filter_ArrayToXml
 * Converts an array to XML
 *
 * @category    Eti
 * @package     Eti_Filter
 *
 */
class Eti_Filter_ArrayToXml implements Zend_Filter_Interface
{
    /**
     * Encoding for the output xml
     * Defaults to UTF-8
     *
     * @var string
     */
    protected $_encoding = 'UTF-8';
    protected $_root = 'response';
    protected $_dom;

    /**
     * Set the input encoding for the given string
     *
     * @param  string $encoding
     */
    public function setEncoding($encoding = null)
    {
        $this->_encoding = (string)$encoding;
        return $this;
    }
    
    public function setRoot($name)
    {
        $this->_root = (string)$name;
        return $this;
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the array $value as an xml string
     *
     * @param  array $value
     * @return string
     */
    public function filter($value)
    {
        if(!is_array($value)) {
            throw new Zend_Filter_Exception('Given value is not an array');
        }
        $this->_dom = new DOMDocument('1.0', $this->_encoding);        
        $xml = $this->_dom->createElement($this->_root);
        $xml = $this->_arrayKeysToAttributes($xml, $value);       
        $this->_dom->appendChild($xml);
        return $this->_dom->saveXML();
    }
    
    protected function _arrayKeysToAttributes(DOMElement $xml, array $array)
    {
        foreach($array as $k => $v) {
            if(is_array($v)) {
                $el = $this->_dom->createElement($k);
                $el = $this->_arrayKeysToAttributes($el, $v);
                $xml->appendChild($el);
            }
            else {
                $xml->setAttribute($k, $v);
            }
        }
        return $xml;       
    }
}