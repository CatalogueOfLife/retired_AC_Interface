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
        $dom = new DOMDocument('1.0', $this->_encoding);
        $xml = $dom->createElement($this->_root);
        foreach($value as $k => $v) {
            $xml->setAttribute($k, $v);
        }
        $dom->appendChild($xml);
        return $dom->saveXML();
    }
}